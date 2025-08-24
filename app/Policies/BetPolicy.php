<?php

namespace App\Policies;

use App\Enums\BetStatus;
use App\Models\Bet;
use App\Models\User;

class BetPolicy
{
    /**
     * Determine whether the user can view any bets.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Bets are publicly viewable
    }

    /**
     * Determine whether the user can view the bet.
     */
    public function view(?User $user, Bet $bet): bool
    {
        return !$bet->is_hidden; // Hidden bets might be staff-only
    }

    /**
     * Determine whether the user can create bets.
     */
    public function create(?User $user): bool
    {
        return can_create_bet($user);
    }

    /**
     * Determine whether the user can update the bet.
     */
    public function update(?User $user, Bet $bet): bool
    {
        return can_edit_bet($user, $bet);
    }

    /**
     * Determine whether the user can delete the bet.
     */
    public function delete(?User $user, Bet $bet): bool
    {
        return can_delete_bet($user, $bet);
    }

    /**
     * Determine whether the user can place bets on this bet.
     */
    public function bet(?User $user, Bet $bet): bool
    {
        if (!$user) {
            return false;
        }

        // Check basic bet permission
        if (!can_bet($user, $bet)) {
            return false;
        }

        // Check if bet is open for betting
        if (!$bet->isOpenForBetting()) {
            return false;
        }

        // Check if user already has a bet on this
        if ($bet->entries()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can close/resolve the bet.
     */
    public function close(?User $user, Bet $bet): bool
    {
        if (!$user) {
            return false;
        }

        // Can't close already completed bets
        if ($bet->status->isFinal()) {
            return false;
        }

        return can_close_bet($user, $bet);
    }

    /**
     * Determine whether the user can cancel the bet.
     */
    public function cancel(?User $user, Bet $bet): bool
    {
        if (!$user) {
            return false;
        }

        // Can't cancel already completed bets
        if ($bet->status === BetStatus::COMPLETED) {
            return false;
        }

        return can_close_bet($user, $bet);
    }
}
