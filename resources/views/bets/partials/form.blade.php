<div class="bet__page-edit-create">
    <div class="bet__header-container">
        <h1 class="bet__header-title"> <a href="/bets">Bets</a> | <a href="/{{ $titleText }}">{{ $titleText }}</a></h1>
    </div>
    <form class='bet__form' method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

    <div class="bet__name-container">
            <label for="name">Bet</label>
            <input class="form__text" type="text" name="name" id="name"
                placeholder="Example: Izzy vs Strickland in UFC 300 (required)"
                value="{{ old('name', $bet->name ?? '') }}" required>
            <small>Enter a title for the bet.</small>
        </div>

    <div class="bet__description-container">
            <label for="description">Overview</label>
            <textarea class="form__text" name="description" id="description"
                placeholder="Enter a brief summary or overview of what members are betting on.">{{ old('description', $bet->description ?? '') }}</textarea>
            <small>Enter a brief summary or overview of what members are betting on, bbcode allowed.</small>
        </div>

    <div class="bet__outcomes-container">
            <label>Possible Outcomes</label>
                <small>
                    Provide {{ config('betting.min_outcomes', 2) }} to {{ config('betting.max_outcomes', 5) }} possible outcomes for this bet.<br>
                    For example, list the teams, nominees, or choices members can wager on.<br>
                    You can edit outcomes until the first wager is placed.
                </small>
            <div class="bet__outcomes-options-container">
                @for ($i = 1; $i <= config('betting.max_outcomes', 5); $i++)
                    <input class="form__text" type="text" name="outcomes[]" id="outcome{{ $i }}"
                        placeholder="Option {{ $i }}{{ $i <= config('betting.min_outcomes', 2) ? ' (required)' : '' }}"
                        value="{{ old('outcomes.' . ($i - 1), isset($bet->outcomes[$i - 1]) ? $bet->outcomes[$i - 1]->name : '') }}"
                        {{ $i <= config('betting.min_outcomes', 2) ? 'required' : '' }}>
                @endfor
            </div>
        </div>

    <div class="bet__payout-info-container">
            <label for="payout_info">Payouts</label>
                <small>
                    Winners share the total pot for their chosen outcome, divided proportionally by the amount each member bet.<br>
                    For example, if three users bet on the winning outcome, each receives a percentage of the pot equal to their contribution.<br>
                    The minimum and maximum bet amounts help ensure fair participation for all members.
                </small>
        </div>

    <div id="min_bet_virtual_select"></div>

    <div class="bet__closing-time-container">
            <label for="closing_time">Expiry Date/Time</label>
            <input class="form__text" type="datetime-local" name="closing_time" id="closing_time"
                value="{{ old('closing_time', isset($bet) && $bet->closing_time ? $bet->closing_time->format('Y-m-d\TH:i') : now()->addHours(config('betting.default_duration_hours', 24))->format('Y-m-d\TH:i')) }}"
                min="{{ now()->addMinutes(config('betting.min_duration_minutes', 60))->format('Y-m-d\TH:i') }}"
                max="{{ now()->addDays(config('betting.max_duration_days', 30))->format('Y-m-d\TH:i') }}"
                {{ old('is_open_ended', $bet->is_open_ended ?? false) ? '' : 'required' }}>
            <small>
                Set the date and time when betting will close. For scheduled events, use the event start time.<br>
                If you want the bet to remain open indefinitely, select "Open Ended" instead.<br>
                The closing time must be at least {{ config('betting.min_duration_minutes', 60) }} minutes from now, and cannot be set more than {{ config('betting.max_duration_days', 30) }} days ahead.
            </small>
        </div>

    <div class="bet__extra-options-container">
            <input type="checkbox" name="is_open_ended" id="is_open_ended" value="1"
                {{ old('is_open_ended', $bet->is_open_ended ?? false) ? 'checked' : '' }}>
            <label for="is_open_ended">Open Ended</label>
        </div>

    <button class="form__button form__button--filled" type="submit">{{ $buttonText ?? 'Open' }}</button>
    </form>

    @section('scripts')
        <script src="{{ asset('build/unit3d/virtual-select.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const closingTimeInput = document.getElementById('closing_time');
                const openEndedCheckbox = document.getElementById('is_open_ended');
                
                // Handle open-ended checkbox
                function toggleClosingTimeRequired() {
                    if (openEndedCheckbox.checked) {
                        closingTimeInput.disabled = true;
                        closingTimeInput.removeAttribute('required');
                        closingTimeInput.value = '';
                        closingTimeInput.classList.add('disabled-field');
                    } else {
                        closingTimeInput.disabled = false;
                        closingTimeInput.setAttribute('required', 'required');
                        closingTimeInput.classList.remove('disabled-field');
                        if (!closingTimeInput.value) {
                            closingTimeInput.value = "{{ now()->addHours(config('betting.default_duration_hours', 24))->format('Y-m-d\TH:i') }}";
                        }
                    }
                }
                
                openEndedCheckbox.addEventListener('change', toggleClosingTimeRequired);
                
                // Initialize state on page load
                toggleClosingTimeRequired();

                VirtualSelect.init({
                    ele: '#min_bet_virtual_select',
                    options: [
                        @foreach(config('betting.allowed_min_bets', [1000, 10000, 100000]) as $minBet)
                        {
                            label: '{{ number_format($minBet) }} to {{ number_format($minBet * config("betting.max_bet_multiplier", 10)) }} BON',
                            value: '{{ $minBet }}'
                        }{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    ],
                    name: 'min_bet',
                    required: true,
                    selectedValue: "{{ old('min_bet', $bet->min_bet ?? config('betting.allowed_min_bets.0', 1000)) }}"
                });
            });
        </script>
    </div>
@endsection
