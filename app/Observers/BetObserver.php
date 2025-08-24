<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Bet;
use App\Repositories\ChatRepository;
use Illuminate\Support\Facades\Log;

class BetObserver
{
    public function created(Bet $bet): void
    {
        try {
            $chat = app(ChatRepository::class);

            $user = $bet->user ?? null;
            
            // Defensive check for user existence
            if (!$user || !$user->exists) {
                Log::warning('BetObserver: Bet created without valid user', ['bet_id' => $bet->id]);
                return;
            }

            $username = '[url=' . href_profile($user) . ']' . $user->username . '[/url]';
            $betUrl = href_bet($bet);
            $betName = $bet->name ?? 'Unnamed Bet';

            $message = $username . ' has created a new Bet [url=' . $betUrl . ']' . $betName . '[/url]';
            
            $chat->systemMessage($message);
            
        } catch (\Exception $e) {
            // Log the error but don't fail the bet creation
            Log::error('BetObserver: Failed to send chat message for new bet', [
                'bet_id' => $bet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}