<?php

namespace App\Events;

use App\Models\Bet;
use App\Models\BetEntry;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetEntryCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public BetEntry $entry,
        public Bet $bet,
        public User $user
    ) {
    }
}
