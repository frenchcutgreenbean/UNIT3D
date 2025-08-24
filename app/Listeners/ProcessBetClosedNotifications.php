<?php

namespace App\Listeners;

use App\Events\BetClosed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessBetClosedNotifications implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(BetClosed $event): void
    {
        try {
            $bet = $event->bet;
            $winnerId = $event->winningOutcome->id;
            
            $entries = $bet->entries()->with('user')->get();

            foreach ($entries->groupBy('user_id') as $userEntries) {
                $user = $userEntries->first()->user;
                if (!$user) continue;

                $userWinningEntries = $userEntries->where('bet_outcome_id', $winnerId);

                if ($userWinningEntries->isNotEmpty()) {
                    $payout = $userWinningEntries->sum('payout') ?: 0;
                    $user->notify(new \App\Notifications\BetClosed('won', $bet, (float) $payout));
                } else {
                    $user->notify(new \App\Notifications\BetClosed('lost', $bet, null));
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to process bet closed notifications', [
                'bet_id' => $event->bet->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
