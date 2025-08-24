<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Bet;
use App\Repositories\ChatRepository;

class BetObserver
{
    public function created(Bet $bet): void
    {
        $chat = app(ChatRepository::class);

        $user = $bet->user ?? null;
        $username = $user
            ? '[url=' . href_profile($user) . ']' . $user->username . '[/url]'
            : 'A user';

        $chat->systemMessage($username . ' has created a new bet [url=' . href_bet($bet) . ']' . $bet->name . '[/url]');
    }
}