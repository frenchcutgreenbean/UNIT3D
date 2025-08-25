<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBetRequest;
use App\Http\Requests\StoreBetEntryRequest;
use App\Http\Requests\CloseBetRequest;
use App\Models\Bet;
use App\Services\BetService;
use App\Services\BetEntryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BetController extends Controller
{
    protected BetService $betService;
    protected BetEntryService $betEntryService;

    public function __construct(BetService $betService, BetEntryService $betEntryService)
    {
        $this->betService = $betService;
        $this->betEntryService = $betEntryService;
    }
    /**
     * Display the bets index page.
     */
    public function index(): View
    {
        $itemsPerPage = config('betting.items_per_page', 10);
        
        $bets = Bet::with(['user', 'outcomes'])
            ->latest()
            ->paginate($itemsPerPage);

        return view('bets.index', compact('bets'));
    }

    /**
     * Show the form for creating a new bet.
     */
    public function create(Request $request)
    {
        abort_unless(can_create_bet($request->user()), 403);

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
        abort_unless(can_create_bet($user), 403);

        $validated = $request->validated();
        
        // Handle open-ended logic
        $isOpenEnded = $request->boolean('is_open_ended');
        $closingTime = $isOpenEnded ? null : $validated['closing_time'];

        try {
            $bet = $this->betService->createBet([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'closing_time' => $closingTime,
                'min_bet' => $validated['min_bet'],
                'is_open_ended' => $isOpenEnded,
                'outcomes' => $validated['outcomes'],
            ], $user);

            return redirect()->route('bets.show', $bet->id)
                ->with('success', 'Bet created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
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

        // Calculate betting statistics
        $houseEdge = (float) config('betting.payout.house_edge', 0.05);
        $totalPot = (float) $bet->pot_size;
        $payoutPot = $totalPot * (1 - $houseEdge);
        $showOdds = config('betting.show_odds', true);
        $showExpectedPayout = config('betting.show_expected_payout', true);

        $outcomeStats = [];
        foreach ($bet->outcomes as $outcome) {
            $outcomeTotal = (float) $outcome->entries->sum('amount');
            $odds = ($outcomeTotal > 0) ? ($payoutPot / $outcomeTotal) : 0;

            $expectedPerEntry = [];
            foreach ($outcome->entries as $entry) {
                $expectedPerEntry[$entry->id] = ($outcomeTotal > 0)
                    ? ($entry->amount / $outcomeTotal) * $payoutPot
                    : 0;
            }

            $outcomeStats[$outcome->id] = [
                'outcomeTotal' => $outcomeTotal,
                'odds' => $odds,
                'expectedPerEntry' => $expectedPerEntry,
            ];
        }

        return view('bets.show', [
            'bet' => $bet,
            'user' => $request->user(),
            'userHasAlreadyBet' => $userHasAlreadyBet,
            'totalPot' => $totalPot,
            'payoutPot' => $payoutPot,
            'showOdds' => $showOdds,
            'showExpectedPayout' => $showExpectedPayout,
            'outcomeStats' => $outcomeStats,
        ]);
    }

    /**
     * Show the form for editing the specified bet.
     */
    public function edit(Request $request, Bet $bet)
    {
        abort_unless(can_edit_bet($request->user(), $bet), 403);
        
        // Only allow staff to edit bets with entries
        $user = $request->user();
        $isModerator = !empty($user->group->is_modo);
        $hasEntries = $bet->entries()->count() > 0;
        
        if ($hasEntries && !$isModerator) {
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
        abort_unless(can_edit_bet($request->user(), $bet), 403);

        $user = $request->user();
        $isModerator = !empty($user->group->is_modo);
        $hasEntries = $bet->entries()->count() > 0;
        
        // Only allow staff to update bets with entries
        if ($hasEntries && !$isModerator) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', 'Cannot edit bet after entries have been made.');
        }

        $validated = $request->validated();
        
        // Handle open-ended logic
        $isOpenEnded = $request->boolean('is_open_ended');
        $closingTime = $isOpenEnded ? null : $validated['closing_time'];

        // If bet has entries, don't allow min_bet changes
        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'closing_time' => $closingTime,
            'is_open_ended' => $isOpenEnded,
        ];

        // Only allow min_bet changes if no entries exist
        if (!$hasEntries) {
            $updateData['min_bet'] = $validated['min_bet'];
        }

        try {
            $this->betService->updateBet($bet, $updateData);

            // Handle outcomes - allow modifications even with entries for staff
            if (isset($validated['outcomes']) && $isModerator) {
                $this->betService->updateBetOutcomes($bet, $validated['outcomes']);
            } elseif (isset($validated['outcomes']) && !$hasEntries) {
                $this->betService->updateBetOutcomes($bet, $validated['outcomes']);
            }

            return redirect()->route('bets.show', $bet->id)
                ->with('success', 'Bet updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified bet from the database.
     */
    public function destroy(Request $request, Bet $bet)
    {
        $user = $request->user();
        abort_unless(can_delete_bet($user, $bet), 403);

        try {
            $this->betService->deleteBet($bet);

            return redirect()->route('bets.index')
                ->with('success', 'Bet deleted and entries refunded successfully!');
        } catch (\Exception $e) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Store a bet entry.
     */
    public function storeEntry(StoreBetEntryRequest $request, Bet $bet)
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $this->betEntryService->createEntry($bet, $user, $validated);

            return redirect()->route('bets.show', $bet->id)
                ->with('success', 'Your bet has been placed successfully!');
        } catch (\Exception $e) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Close a bet and determine winner (moderators only).
     */
    public function close(CloseBetRequest $request, Bet $bet)
    {
        abort_unless(can_close_bet($request->user(), $bet), 403);
        $validated = $request->validated();
        
        try {
            $this->betService->closeBet($bet, $validated['winner_outcome_id']);

            return redirect()->route('bets.show', $bet->id)
                ->with('success', 'Bet has been closed and payouts distributed!');
        } catch (\Exception $e) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a bet and refund all entries.
     */
    public function cancel(Request $request, Bet $bet)
    {
        abort_unless(can_close_bet($request->user(), $bet), 403);

        try {
            $this->betService->cancelBet($bet);

            return redirect()->route('bets.show', $bet->id)
                ->with('success', 'Bet has been cancelled and all entries refunded!');
        } catch (\Exception $e) {
            return redirect()->route('bets.show', $bet->id)
                ->with('error', $e->getMessage());
        }
    }
}
