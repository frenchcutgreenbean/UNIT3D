<?php

namespace App\Services;

use App\Enums\BetStatus;
use App\Events\BetCreated;
use App\Events\BetClosed;
use App\Events\BetCancelled;
use App\Models\Bet;
use App\Models\User;
use App\Notifications\BetClosed as BetClosedNotification;
use App\Notifications\BetDeleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BetService
{
    /**
     * Create a new bet with outcomes.
     */
    public function createBet(array $data, User $user): Bet
    {
        return DB::transaction(function () use ($data, $user) {
            // Validate outcomes
            $outcomes = array_filter($data['outcomes'], fn($o) => trim($o) !== '');
            
            $minOutcomes = config('betting.min_outcomes', 2);
            $maxOutcomes = config('betting.max_outcomes', 5);
            
            if (count($outcomes) < $minOutcomes) {
                throw new \Exception("At least {$minOutcomes} outcomes are required.");
            }
            
            if (count($outcomes) > $maxOutcomes) {
                throw new \Exception("Maximum {$maxOutcomes} outcomes are allowed.");
            }

            $bet = Bet::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'closing_time' => $data['closing_time'],
                'min_bet' => $data['min_bet'],
                'user_id' => $user->id,
                'status' => BetStatus::OPEN,
                'is_open_ended' => $data['is_open_ended'] ?? false,
            ]);
            
            foreach ($outcomes as $outcomeName) {
                $bet->outcomes()->create(['name' => $outcomeName]);
            }
            
            // Dispatch event for bet creation
            BetCreated::dispatch($bet, $user);
            
            return $bet;
        });
    }

    /**
     * Update an existing bet.
     */
    public function updateBet(Bet $bet, array $data): Bet
    {
        $bet->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'closing_time' => $data['closing_time'] ?? null,
            'min_bet' => $data['min_bet'] ?? null,
            'is_open_ended' => $data['is_open_ended'] ?? null,
        ], fn($value) => $value !== null));

        return $bet;
    }

    /**
     * Update bet outcomes (staff only for bets with entries).
     */
    public function updateBetOutcomes(Bet $bet, array $outcomes): void
    {
        $filteredOutcomes = array_filter($outcomes, fn($o) => trim($o) !== '');
        
        $minOutcomes = config('betting.min_outcomes', 2);
        $maxOutcomes = config('betting.max_outcomes', 5);
        
        if (count($filteredOutcomes) < $minOutcomes) {
            throw new \Exception("At least {$minOutcomes} outcomes are required.");
        }
        
        if (count($filteredOutcomes) > $maxOutcomes) {
            throw new \Exception("Maximum {$maxOutcomes} outcomes are allowed.");
        }

        DB::transaction(function () use ($bet, $filteredOutcomes) {
            // Get existing outcomes
            $existingOutcomes = $bet->outcomes()->get();
            
            // Update or create outcomes
            foreach ($filteredOutcomes as $index => $outcomeName) {
                if (isset($existingOutcomes[$index])) {
                    // Update existing outcome
                    $existingOutcomes[$index]->update(['name' => $outcomeName]);
                } else {
                    // Create new outcome
                    $bet->outcomes()->create(['name' => $outcomeName]);
                }
            }
            
            // Remove extra outcomes (only if they have no entries)
            if (count($existingOutcomes) > count($filteredOutcomes)) {
                $outcomesToRemove = $existingOutcomes->slice(count($filteredOutcomes));
                foreach ($outcomesToRemove as $outcome) {
                    if ($outcome->entries()->count() === 0) {
                        $outcome->delete();
                    }
                }
            }
        });
    }

    /**
     * Close a bet with winner determination.
     */
    public function closeBet(Bet $bet, int $winnerOutcomeId): void
    {
        DB::transaction(function () use ($bet, $winnerOutcomeId) {
            $bet->update([
                'status' => BetStatus::COMPLETED,
                'winner_outcome_id' => $winnerOutcomeId,
            ]);

            $this->distributePayout($bet, $winnerOutcomeId);
            
            // Dispatch event instead of handling notifications directly
            $winnerOutcome = $bet->outcomes()->find($winnerOutcomeId);
            if ($winnerOutcome) {
                BetClosed::dispatch($bet, $winnerOutcome, auth()->user());
            }
        });
    }

    /**
     * Cancel a bet and refund all entries.
     */
    public function cancelBet(Bet $bet): void
    {
        if ($bet->status === BetStatus::COMPLETED) {
            throw new \Exception('Cannot cancel a completed bet.');
        }

        DB::transaction(function () use ($bet) {
            $bet->update(['status' => BetStatus::CANCELLED]);
            
            $this->refundAllEntries($bet, 'cancelled');
            
            // Dispatch cancellation event
            BetCancelled::dispatch($bet, auth()->user());
        });
    }

    /**
     * Delete a bet and refund entries.
     */
    public function deleteBet(Bet $bet): void
    {
        if ($bet->status === BetStatus::COMPLETED) {
            throw new \Exception('Cannot delete a completed bet.');
        }

        DB::transaction(function () use ($bet) {
            $this->refundAllEntries($bet, 'deleted');
            
            $bet->entries()->delete();
            $bet->outcomes()->delete();
            $bet->comments()->delete();
            $bet->delete();
        });
    }

    /**
     * Refund all entries for a bet.
     */
    private function refundAllEntries(Bet $bet, string $reason): void
    {
        $refunds = $bet->entries()
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->groupBy('user_id')
            ->get();

        foreach ($refunds as $refund) {
            $user = User::find($refund->user_id);
            if (!$user) {
                Log::warning("Bet refund: user not found", [
                    'user_id' => $refund->user_id, 
                    'bet_id' => $bet->id
                ]);
                continue;
            }

            $amount = (float) $refund->total;
            if ($amount > 0) {
                $user->increment('seedbonus', $amount);

                if ($reason === 'deleted') {
                    $user->notify(new BetDeleted($bet->name, $amount));
                } elseif ($reason === 'cancelled') {
                    $user->notify(new BetClosedNotification('cancelled', $bet, $amount));
                }

                Log::info("Bet refund processed", [
                    'user_id' => $refund->user_id,
                    'bet_id' => $bet->id,
                    'amount' => $amount,
                    'reason' => $reason
                ]);
            }
        }
    }

    /**
     * Calculate and distribute payouts for a completed bet.
     */
    private function distributePayout(Bet $bet, int $winnerOutcomeId): void
    {
        $totalPot = $bet->entries()->sum('amount');
        $winningEntries = $bet->entries()->where('bet_outcome_id', $winnerOutcomeId)->get();
        $totalWinningAmount = $winningEntries->sum('amount');

        if ($totalWinningAmount == 0) {
            return; // No winners, pot goes to house
        }

        // Apply house edge from config
        $houseEdge = config('betting.payout.house_edge', 0.05);
        $payoutPot = $totalPot * (1 - $houseEdge);

        foreach ($winningEntries as $entry) {
            $payout = ($entry->amount / $totalWinningAmount) * $payoutPot;
            $entry->update(['payout' => $payout]);
            $entry->user->increment('seedbonus', $payout);
        }
    }
}
