<?php

namespace App\Listeners;

use App\Events\BetCreated;
use App\Repositories\ChatRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendBetCreatedChatMessage implements ShouldQueue
{
    public function __construct(
        private ChatRepository $chatRepository
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(BetCreated $event): void
    {
        try {
            $user = $event->creator;
            $bet = $event->bet;

            if (!$user || !$user->exists || !$bet || !$bet->exists) {
                return;
            }

            $username = '[url=' . href_profile($user) . ']' . $user->username . '[/url]';
            $betUrl = href_bet($bet);
            $betName = $bet->name ?? 'Unnamed Bet';

            $message = $username . ' has created a new Bet [url=' . $betUrl . ']' . $betName . '[/url]';
            
            $this->chatRepository->systemMessage($message);
            
        } catch (\Exception $e) {
            Log::error('Failed to send bet created chat message', [
                'bet_id' => $event->bet->id,
                'user_id' => $event->creator->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
