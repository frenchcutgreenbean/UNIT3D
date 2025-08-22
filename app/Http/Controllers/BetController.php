<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBetRequest;
use App\Http\Requests\StoreBetEntryRequest;
use App\Http\Requests\Staff\CloseBetRequest;
use App\Models\Bet;
use App\Models\BetEntry;
use App\Models\BetOutcome;
use App\Notifications\BetClosed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BetController extends Controller
{
    /**
     * Display the bets index page.
     */
    public function index(Request $request)
    {
        return view('bets.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show the form for creating a new bet.
     */
    public function create(Request $request)
    {
        return view('bets.create', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Store a newly created bet in the database.
     */
    public function store(StoreBetRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        
        // Handle open-ended logic
        $isOpenEnded = $request->boolean('is_open_ended');
        $closingTime = $isOpenEnded ? null : $validated['closing_time'];

        $bet = Bet::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'closing_time' => $closingTime,
            'min_bet' => $validated['min_bet'],
            'user_id' => $user->id,
            'status' => 'open',
            'is_open_ended' => $isOpenEnded,
        ]);
        
        $outcomes = array_filter($validated['outcomes'], fn($o) => trim($o) !== '');

        if (count($outcomes) < 2) {
            return back()->withErrors(['outcomes' => 'At least 2 options are required.'])->withInput();
        }

        foreach ($outcomes as $outcomeName) {
            $bet->outcomes()->create(['name' => $outcomeName]);
        }

        return redirect()->route('bets.show', $bet->id)
            ->with('success', 'Bet created successfully!');
    }

    /**
     * Display the specified bet.
     */
    public function show(Request $request, Bet $bet)
    {
        // Eager load outcomes and entries
        $bet->load(['outcomes.entries.user', 'user']);
        
        $userHasAlreadyBet = $request->user() 
            ? $bet->entries()->where('user_id', $request->user()->id)->exists()
            : false;

        return view('bets.show', [
            'bet' => $bet,
            'user' => $request->user(),
            'userHasAlreadyBet' => $userHasAlreadyBet,
        ]);
    }

    /**
     * Show the form for editing the specified bet.
     */
    public function edit(Request $request, Bet $bet)
    {
        // Only allow editing if no bets have been placed and user is the creator
        if ($bet->user_id !== $request->user()->id) {
            abort(403, 'You can only edit your own bets.');
        }
        
        if ($bet->entries()->count() > 0) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', 'Cannot edit bet after entries have been made.');
        }

        return view('bets.edit', [
            'bet' => $bet,
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the specified bet in the database.
     */
    public function update(StoreBetRequest $request, Bet $bet)
    {
        // Only allow editing if no bets have been placed and user is the creator
        if ($bet->user_id !== $request->user()->id) {
            abort(403, 'You can only edit your own bets.');
        }
        
        if ($bet->entries()->count() > 0) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', 'Cannot edit bet after entries have been made.');
        }

        $validated = $request->validated();
        
        // Handle open-ended logic
        $isOpenEnded = $request->boolean('is_open_ended');
        $closingTime = $isOpenEnded ? null : $validated['closing_time'];

        $bet->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'closing_time' => $closingTime,
            'min_bet' => $validated['min_bet'],
            'is_open_ended' => $isOpenEnded,
        ]);

        return redirect()->route('bets.show', $bet->id)
            ->with('success', 'Bet updated successfully!');
    }

    /**
     * Remove the specified bet from the database.
     */
    public function destroy(Request $request, Bet $bet)
    {
        $user = $request->user();
        
        // Allow deletion if no bets have been placed AND (user is creator OR user is moderator)
        if ($bet->entries()->count() > 0) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', 'Cannot delete bet after entries have been made.');
        }
        
        // Check permissions: owner or moderator
        if ($bet->user_id !== $user->id && !$user->group->is_modo) {
            abort(403, 'You can only delete your own bets unless you are a moderator.');
        }

        $bet->delete();

        return redirect()->route('bets.index')
            ->with('success', 'Bet deleted successfully!');
    }

    /**
     * Store a bet entry.
     */
    public function storeEntry(StoreBetEntryRequest $request, Bet $bet)
    {
        $validated = $request->validated();
        $user = $request->user();

        // Deduct points from user
        $user->decrement('seedbonus', $validated['amount']);

        // Create bet entry
        $bet->entries()->create([
            'bet_outcome_id' => $validated['bet_outcome_id'],
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'anon' => $validated['anon'] ?? false,
        ]);

        return redirect()->route('bets.show', $bet->id)
            ->with('success', 'Your bet has been placed successfully!');
    }

    /**
     * Close a bet and determine winner (moderators only).
     */
    public function close(CloseBetRequest $request, Bet $bet)
    {
        $validated = $request->validated();
        
        // Update bet status and winner
        $bet->update([
            'status' => 'completed',
            'winner_outcome_id' => $validated['winner_outcome_id'],
        ]);

        // Calculate and distribute payouts
        $this->distributePayout($bet, $validated['winner_outcome_id']);

        // --- notify participants of result ---
        $winnerId = $validated['winner_outcome_id'];
        $entries = $bet->entries()->with('user')->get(); // all entries with users

        foreach ($entries->groupBy('user_id') as $userEntries) {
            $user = $userEntries->first()->user;
            if (! $user) {
                continue;
            }

            $userWinningEntries = $userEntries->where('bet_outcome_id', $winnerId);

            if ($userWinningEntries->isNotEmpty()) {
                // prefer stored payout (distributePayout sets 'payout'), fallback to sum
                $payout = $userWinningEntries->sum('payout') ?: 0;
                $user->notify(new BetClosed('won', $bet, (float) $payout));
            } else {
                $user->notify(new BetClosed('lost', $bet, null));
            }
        }
        // --- end notify ---

        return redirect()->route('bets.show', $bet->id)
            ->with('success', 'Bet has been closed and payouts distributed!');
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
            // No winners, pot goes to house
            return;
        }

        foreach ($winningEntries as $entry) {
            // Calculate payout: (entry amount / total winning amount) * total pot
            $payout = ($entry->amount / $totalWinningAmount) * $totalPot;
            
            // Update entry with payout
            $entry->update(['payout' => $payout]);
            
            // Give payout to user
            $entry->user->increment('seedbonus', $payout);
        }
    }
}
