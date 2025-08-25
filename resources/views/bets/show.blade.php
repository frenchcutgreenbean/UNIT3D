@extends('layout.with-main')

@section('title')
    <title>{{ $bet->name }} - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li><a href="{{ route('bets.index') }}">Bets</a></li>
    <li class="breadcrumb--active">{{ $bet->name }}</li>
@endsection

@section('page', 'page__bets--show')

@section('main')
    @if ($bet->status === \App\Enums\BetStatus::COMPLETED)
        <div class="bet-completed-alert">
            <strong>The outcome for this bet has been determined.</strong><br>
            The winning outcome was <strong>{{ $bet->winnerOutcome->name ?? 'N/A' }}</strong>
        </div>
    @endif
    @if ($bet->status === \App\Enums\BetStatus::CANCELLED)
        <div class="bet-cancelled-alert">
            <strong>This bet has been cancelled.</strong><br>
            All entries have been refunded.
        </div>
    @endif
    @if ($bet->hasExpired() && $bet->status === \App\Enums\BetStatus::CLOSED)
        <div class="bet-expired-alert">
            <strong>This bet has expired</strong> but is awaiting moderator review.
        </div>
    @endif
    <div class="bet__page-show-container">
        <div class="bet__header-container">
            <h1 class="bet__header-title"> <a href="/bets">Bets</a>
                {{-- Edit/Delete buttons for bet creator when no entries exist --}}
                @if (can_edit_bet($user, $bet) && $bet->status !== \App\Enums\BetStatus::COMPLETED)
                    | <a href="{{ route('bets.edit', $bet->id) }}">Edit</a>
                @endif
            </h1>
        </div>
        <div class="bet__title-container">
            <div class="bet__info">
                <h1 class="bet__title">{{ $bet->name }}</h1>
            </div>
            @if (can_delete_bet($user, $bet))
                <div class="bet__action-buttons">
                    <form method="POST" action="{{ route('bets.destroy', $bet->id) }}" class="inline-form"
                        onsubmit="return confirm('Are you sure you want to delete this bet?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="form__button form__button--danger">Delete</button>
                    </form>
                </div>
            @endif
        </div>
        <div class="bet__main-container">
            <div class="bet__main-left">
                <div class="bet__meta-container">
                    <div class="bet__meta">
                        <div class="bet__creator">Opened by:
                            <x-user-tag :user="$bet->user" :anon="false" />
                        </div>
                        <div class="bet__time-info">Created on: <time
                                datetime="{{ $bet->created_at }}">{{ $bet->created_at->format('M d, Y H:i') }}</time>
                        </div>
                        <div class="bet__close-info">
                            @if ($bet->is_open_ended)
                                <strong>Open-ended bet</strong>
                            @else
                                Closes @ <time
                                    datetime="{{ $bet->closing_time }}">{{ $bet->closing_time->format('M d, Y H:i') }}</time>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($bet->description)
                    <div class="bet__description">
                        @bbcode($bet->description)
                    </div>
                @endif

                <table class="bet__stats-table">
                    <tr>
                        <td><strong>Bet Range:</strong></td>
                        <td>{{ number_format($bet->min_bet) }} - {{ number_format($bet->min_bet * 10) }} BON</td>
                    </tr>
                    <tr>
                        <td><strong>Current Pot:</strong></td>
                        <td>{{ number_format($bet->pot_size) }} BON</td>
                    </tr>
                    <tr>
                        <td><strong>Total Members:</strong></td>
                        <td>{{ $bet->total_entries }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if ($bet->status === \App\Enums\BetStatus::COMPLETED)
                                <span class="badge badge-success">Completed</span>
                            @elseif ($bet->status === \App\Enums\BetStatus::CANCELLED)
                                <span class="badge badge-secondary">Cancelled</span>
                            @elseif ($bet->hasExpired())
                                <span class="badge badge-warning">Expired</span>
                            @else
                                <span class="badge badge-primary">Open</span>
                            @endif
                        </td>
                    </tr>
                    @if (!$bet->is_open_ended)
                        <tr>
                            <td><strong>Expires:</strong></td>
                            <td>
                                {{ $bet->closing_time->diffForHumans() }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Last Activity:</strong></td>
                        <td>{{ $bet->activity ?? 'No activity yet' }}</td>
                    </tr>
                </table>
            </div>

            <div class="bet__main-right">
                <div class="bet__status">
                    <i
                        class="fas {{ $bet->status === \App\Enums\BetStatus::COMPLETED || $bet->status === \App\Enums\BetStatus::CANCELLED || $bet->hasExpired() ? 'fa-lock' : 'fa-lock-open' }}"></i>
                </div>
                {{-- Moderator controls --}}
                @if (can_close_bet($user, $bet) && $bet->status !== \App\Enums\BetStatus::COMPLETED && $bet->status !== \App\Enums\BetStatus::CANCELLED)
                    <div class="bet__mod-controls">
                        <form method="POST" action="{{ route('bets.close', $bet->id) }}" class="close-bet-form">
                            @csrf
                            <div class="close-bet-container">
                                <label for="winner_outcome_id"><strong>Select Winner:</strong></label>
                                <select name="winner_outcome_id" id="winner_outcome_id" required class="form__select">
                                    <option value="">Choose winning outcome...</option>
                                    @foreach ($bet->outcomes as $outcome)
                                        <option value="{{ $outcome->id }}">{{ $outcome->name }}</option>
                                    @endforeach
                                </select>
                                <div class="bet__action-buttons">
                                    <button type="submit" class="form__button form__button--danger"
                                        onclick="return confirm('Are you sure? This will close the bet and distribute payouts.')">
                                        Close Bet
                                    </button>
                                    <button type="button" class="form__button form__button--danger"
                                        onclick="if(confirm('Are you sure? This will cancel the bet and refund all entries.')) { document.getElementById('cancel-form').submit(); }">
                                        Cancel Bet
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form id="cancel-form" method="POST" action="{{ route('bets.cancel', $bet->id) }}" style="display: none;">
                            @csrf
                        </form>
                    </div>
                @endif
            </div>
        </div>
        <div class="bet__outcome-container">
            @foreach ($bet->outcomes as $outcome)
                @php
                    $stats = $outcomeStats[$outcome->id] ?? ['outcomeTotal' => 0, 'odds' => 0, 'expectedPerEntry' => []];
                @endphp
                <div class="bet__outcome {{ $bet->status === \App\Enums\BetStatus::CANCELLED ? 'bet__outcome--cancelled' : '' }}">
                    <h3 class="bet__outcome-header">
                        {{ $outcome->name }}
                        <span class="bet__outcome-stats">
                            STAKE: {{ number_format($stats['outcomeTotal']) }} BON
                            ({{ $outcome->entries->count() }} {{ Str::plural('bet', $outcome->entries->count()) }})
                            @if ($showOdds && $bet->status === \App\Enums\BetStatus::OPEN && $totalPot > 0 && $stats['outcomeTotal'] > 0)
                                | ODDS: {{ number_format($stats['odds'], 2) }}:1
                            @endif
                        </span>
                        @if ($bet->status === \App\Enums\BetStatus::COMPLETED && $bet->winner_outcome_id === $outcome->id)
                            <span class="badge badge-success bet__winner-badge">WINNER</span>
                        @endif
                    </h3>

                    @if ($outcome->entries->count() > 0)
                        <table class="bet__outcome-entries-table {{ config('betting.show_expected_payout', true) ? 'show-payout-column' : 'hide-payout-column' }}">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Bet Amount</th>
                                    @if ($bet->status === \App\Enums\BetStatus::COMPLETED)
                                        <th>Payout</th>
                                    @elseif (config('betting.show_expected_payout', true))
                                        <th>Expected Payout</th>
                                    @endif
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outcome->entries as $entry)
                                    <tr>
                                        <td>
                                            <x-user-tag :user="$entry->user" :anon="$entry->anon" />
                                        </td>
                                        <td>{{ number_format($entry->amount) }} BON</td>

                                        @if ($bet->status === \App\Enums\BetStatus::COMPLETED)
                                            <td>
                                                @if ($entry->payout)
                                                    <span class="text-success">{{ number_format($entry->payout, 2) }} BON</span>
                                                @else
                                                    <span class="text-muted">No payout</span>
                                                @endif
                                            </td>
                        @elseif (config('betting.show_expected_payout', true))
                            <td>
                                @if (isset($stats['expectedPerEntry'][$entry->id]))
                                    {{ number_format($stats['expectedPerEntry'][$entry->id], 2) }} BON
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endif                                        <td>{{ $entry->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="bet__no-bets-message">No bets placed on this outcome yet.</p>
                    @endif

                    {{-- Betting form --}}
                    @if (can_bet($user, $bet) && !$userHasAlreadyBet && $bet->isOpenForBetting() && $bet->status === \App\Enums\BetStatus::OPEN)
                        <div class="bet__betting-form-container">
                            <form method="POST" action="{{ route('bets.entries.store', $bet->id) }}">
                                @csrf
                                <input type="hidden" name="bet_outcome_id" value="{{ $outcome->id }}">
                                <div class="bet__betting-form">
                                    <label for="amount_{{ $outcome->id }}"><strong>Bet Amount:</strong></label>
                                    <input type="number" name="amount" id="amount_{{ $outcome->id }}" class="form__text"
                                        placeholder="Enter amount ({{ number_format($bet->min_bet) }} - {{ number_format($bet->min_bet * config('betting.max_bet_multiplier', 10)) }})"
                                        min="{{ $bet->min_bet }}" max="{{ min($bet->min_bet * config('betting.max_bet_multiplier', 10), $user->seedbonus, config('betting.max_bon_amount', 10000000)) }}"
                                        required>
                                    @if (config('betting.anonymous_betting_allowed', true))
                                    <label class="anon-checkbox">
                                        <input type="checkbox" name="anon" value="1"> Anonymous
                                    </label>
                                    @endif
                                    <button type="submit" class="form__button form__button--filled">Place Bet</button>
                                </div>
                                <small class="bet__balance-info">
                                    Your current balance: {{ number_format($user->seedbonus) }} BON
                                </small>
                            </form>
                        </div>
                    @elseif ($user && $userHasAlreadyBet)
                        <div class="bet__already-bet-message">
                            <small><em>You have already placed a bet on this.</em></small>
                        </div>
                    @elseif (!$bet->isOpenForBetting() || $bet->status !== \App\Enums\BetStatus::OPEN)
                        <div class="bet__betting-closed-message">
                            <small><em>Betting is closed for this outcome.</em></small>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <livewire:comments :model="$bet" />
    </div>
@endsection
