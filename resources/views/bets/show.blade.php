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

    @if ($bet->status === 'completed')
        <div class="bet-completed-alert">
            <strong>The outcome for this bet has been determined.</strong><br>
            The winning outcome was <strong>{{ $bet->winnerOutcome->name ?? 'N/A' }}</strong>
        </div>
    @endif

    @if ($bet->hasExpired() && $bet->status === 'open')
        <div class="bet-expired-alert">
            <strong>This bet has expired</strong> but is awaiting moderator review.
        </div>
    @endif

    <div class="page__bets--show">
        <div class="header__container">
            <a href="{{ route('bets.index') }}">Bets</a>
            
            {{-- Edit/Delete buttons for bet creator when no entries exist --}}
            @if ($user && (($bet->user_id === $user->id && $bet->canBeEdited()) || ($user->group->is_modo && $bet->canBeEdited())))
                @if ($bet->user_id === $user->id)
                    <a href="{{ route('bets.edit', $bet->id) }}" class="btn btn-sm btn-primary">Edit</a>
                @endif
                <form method="POST" action="{{ route('bets.destroy', $bet->id) }}" class="inline-form" 
                      onsubmit="return confirm('Are you sure you want to delete this bet?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            @endif
        </div>

        <div class="bet-header">
            <div class="bet-info">
                <span class="bet-title"><strong>{{ $bet->name }}</strong></span>
                <div class="bet-meta">
                    Opened by <x-user-tag :user="$bet->user" :anon="false" />
                    on <time datetime="{{ $bet->created_at }}">{{ $bet->created_at->format('M d, Y H:i') }}</time>
                    @if ($bet->is_open_ended)
                        | <strong>Open-ended bet</strong>
                    @else
                        | Closes @ <time datetime="{{ $bet->closing_time }}">{{ $bet->closing_time->format('M d, Y H:i') }}</time>
                    @endif
                </div>
            </div>
            <div class="bet-controls">
                <span class="fas {{ $bet->status === 'completed' ? 'fa-lock' : 'fa-lock-open' }}"></span>
                {{-- Moderator controls --}}
                @if ($user && $user->group->is_modo && $bet->status !== 'completed')
                    <div class="mod-controls">
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
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure? This will close the bet and distribute payouts.')">
                                    Close Bet
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        @if ($bet->description)
            <div class="bet-description">
                @bbcode($bet->description)
            </div>
        @endif

        <div class="bet-stats">
            <table class="bet-stats-table">
                <tr>
                    <td><strong>Bet Range:</strong></td>
                    <td>{{ number_format($bet->min_bet) }} - {{ number_format($bet->min_bet * 10) }} BP</td>
                </tr>
                <tr>
                    <td><strong>Current Pot:</strong></td>
                    <td>{{ number_format($bet->pot_size) }} BP</td>
                </tr>
                <tr>
                    <td><strong>Total Members:</strong></td>
                    <td>{{ $bet->total_entries }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        @if ($bet->status === 'completed')
                            <span class="badge badge-success">Completed</span>
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
    </div>

    @foreach ($bet->outcomes as $outcome)
        <div class="bet-outcome">
            <h3 class="outcome-header">
                {{ $outcome->name }}
                <span class="outcome-stats">
                    STAKE: {{ number_format($outcome->entries->sum('amount')) }} BP
                    ({{ $outcome->entries->count() }} {{ Str::plural('bet', $outcome->entries->count()) }})
                </span>
                @if ($bet->status === 'completed' && $bet->winner_outcome_id === $outcome->id)
                    <span class="badge badge-success winner-badge">WINNER</span>
                @endif
            </h3>
            
            @if ($outcome->entries->count() > 0)
                <table class="outcome-entries-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Bet Amount</th>
                            <th>When</th>
                            @if ($bet->status === 'completed')
                                <th>Payout</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outcome->entries as $entry)
                            <tr>
                                <td>
                                    <x-user-tag :user="$entry->user" :anon="$entry->anon" />
                                </td>
                                <td>{{ number_format($entry->amount) }} BP</td>
                                <td>{{ $entry->created_at->diffForHumans() }}</td>
                                @if ($bet->status === 'completed')
                                    <td>
                                        @if ($entry->payout)
                                            <span class="text-success">{{ number_format($entry->payout) }} BP</span>
                                        @else
                                            <span class="text-muted">No payout</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-bets-message">No bets placed on this outcome yet.</p>
            @endif

            {{-- Betting form --}}
            @if ($user && !$userHasAlreadyBet && $bet->isOpenForBetting() && $bet->status === 'open')
                <div class="betting-form-container">
                    <form method="POST" action="{{ route('bets.entries.store', $bet->id) }}">
                        @csrf
                        <input type="hidden" name="bet_outcome_id" value="{{ $outcome->id }}">
                        <div class="betting-form">
                            <label for="amount_{{ $outcome->id }}"><strong>Bet Amount:</strong></label>
                            <input type="number" 
                                   name="amount" 
                                   id="amount_{{ $outcome->id }}"
                                   class="form__text" 
                                   placeholder="Enter amount ({{ number_format($bet->min_bet) }} - {{ number_format($bet->min_bet * 10) }})"
                                   min="{{ $bet->min_bet }}"
                                   max="{{ min($bet->min_bet * 10, $user->seedbonus) }}"
                                   required>
                            <label class="anon-checkbox">
                                <input type="checkbox" name="anon" value="1"> Anonymous
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm">Place Bet</button>
                        </div>
                        <small class="balance-info">
                            Your current balance: {{ number_format($user->seedbonus) }} BP
                        </small>
                    </form>
                </div>
            @elseif ($user && $userHasAlreadyBet)
                <div class="already-bet-message">
                    <small><em>You have already placed a bet on this.</em></small>
                </div>
            @elseif (!$bet->isOpenForBetting() || $bet->status !== 'open')
                <div class="betting-closed-message">
                    <small><em>Betting is closed for this outcome.</em></small>
                </div>
            @endif
        </div>
    @endforeach

    @if ($bet->outcomes->isEmpty())
        <div class="no-outcomes-message">
            <p>No outcomes defined for this bet.</p>
        </div>
    @endif

@endsection
