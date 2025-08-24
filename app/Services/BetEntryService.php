<?php

namespace App\Services;

use App\Enums\BetStatus;
use App\Events\BetEntryCreated;
use App\Models\Bet;
use App\Models\BetEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BetEntryService
{
    /**
     * Create a bet entry for a user.
     */
    public function createEntry(Bet $bet, User $user, array $data): BetEntry
    {
        return DB::transaction(function () use ($bet, $user, $data) {
            // Validate bet is still open
            if (!$bet->isOpenForBetting() || $bet->status !== BetStatus::OPEN) {
                throw new \Exception('Bet is no longer accepting entries.');
            }

            // Check if user has sufficient funds
            if ($user->seedbonus < $data['amount']) {
                throw new \Exception('Insufficient BON for this bet.');
            }

            // Check if user already has a bet on this
            if ($bet->entries()->where('user_id', $user->id)->exists()) {
                throw new \Exception('You have already placed a bet on this.');
            }

            // Deduct BON from user
            $user->decrement('seedbonus', $data['amount']);

            // Create the entry
            $entry = $bet->entries()->create([
                'user_id' => $user->id,
                'bet_outcome_id' => $data['bet_outcome_id'],
                'amount' => $data['amount'],
                'anon' => $data['anon'] ?? false,
            ]);
            
            // Dispatch event for bet entry creation
            BetEntryCreated::dispatch($entry, $bet, $user);
            
            return $entry;
        });
    }
}
