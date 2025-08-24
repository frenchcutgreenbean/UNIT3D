<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBetEntryRequest;
use App\Models\Bet;
use App\Models\BetEntry;
use App\Models\BetOutcome;
use Illuminate\Http\Request;

class BetEntryController extends Controller
{
    /**
     * Store a new bet entry (wager) for a bet.
     */
    public function store(StoreBetEntryRequest $request, Bet $bet)
    {
        $user = $request->user();

        // Only allow if bet is open and user can bet (use helper)
        if ($bet->is_closed || ! can_bet($user, $bet) || ($bet->closing_time && now()->gt($bet->closing_time))) {
            return back()->withErrors(['bet' => 'Bet is closed or you cannot bet.']);
        }

        $validated = $request->validated();

        // Check user balance, etc. (Assuming you use BON or similar currency)
        if ($user->seedbonus < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient BON for this wager.']);
        }

        // Deduct BON
        $user->decrement('seedbonus', $validated['amount']);

        // Create the entry
        $entry = $bet->entries()->create([
            'user_id' => $user->id,
            'bet_outcome_id' => $validated['bet_outcome_id'],
            'amount' => $validated['amount'],
            'anon' => $validated['anon'] ?? false,
        ]);

        // Optionally, notify or log

        return redirect()->route('bets.show', $bet->id)
            ->with('success', 'Your wager has been placed!');
    }
}
