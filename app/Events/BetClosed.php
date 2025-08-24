<?php

namespace App\Events;

use App\Models\Bet;
use App\Models\BetOutcome;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Bet $bet,
        public BetOutcome $winningOutcome,
        public User $closedBy
    ) {
    }
}
