<div class="page-edit-create">
    <div class="header__container">
        <a href="/bets">Bets</a>
        <h1 class="header__title">{{ $titleText }}</h1>

    </div>
    <form class='bet-form' method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="bet-name__container">
            <label for="name">Bet</label>
            <input type="text" name="name" id="name"
                placeholder="Example: Izzy vs Strickland in UFC 300 (required)"
                value="{{ old('name', $bet->name ?? '') }}" required>
            <small>Enter a title for the bet.</small>
        </div>

        <div class="bet-description__container">
            <label for="description">Overview</label>
            <textarea name="description" id="description"
                placeholder="Enter a brief summary or overview of what members are betting on.">{{ old('description', $bet->description ?? '') }}</textarea>
        </div>

        <div class="bet-outcomes__container">
            <label>Possible Outcomes</label>
                <small>
                    Provide 2 to 5 possible outcomes for this bet.<br>
                    For example, list the teams, nominees, or choices members can wager on.<br>
                    You can edit outcomes until the first wager is placed.
                </small>
            <div class="bet-outcomes-options__container">
                @for ($i = 1; $i <= 5; $i++)
                    <input type="text" name="outcomes[]" id="outcome{{ $i }}"
                        placeholder="Option {{ $i }}{{ $i <= 2 ? ' (required)' : '' }}"
                        value="{{ old('outcomes.' . ($i - 1), isset($bet->outcomes[$i - 1]) ? $bet->outcomes[$i - 1]->name : '') }}"
                        {{ $i <= 2 ? 'required' : '' }}>
                @endfor
            </div>
        </div>

        <div class="payout-info__container">
            <label for="payout_info">Payouts</label>
                <small>
                    Winners share the total pot for their chosen outcome, divided proportionally by the amount each member bet.<br>
                    For example, if three users bet on the winning outcome, each receives a percentage of the pot equal to their contribution.<br>
                    The minimum and maximum bet amounts help ensure fair participation for all members.
                </small>
        </div>

        <div id="min_bet_virtual_select"></div>

        <div class="closing-time__container">
            <label for="closing_time">Expiry Date/Time</label>
            <input type="datetime-local" name="closing_time" id="closing_time"
                value="{{ old('closing_time', isset($bet) && $bet->closing_time ? $bet->closing_time->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i')) }}"
                min="{{ now()->format('Y-m-d\TH:i') }}"
                max="{{ now()->addYears(5)->format('Y-m-d\TH:i') }}"
                {{ old('is_open_ended', $bet->is_open_ended ?? false) ? '' : 'required' }}>
            <small>
                Set the date and time when betting will close. For scheduled events, use the event start time.<br>
                If you want the bet to remain open indefinitely, select "Open Ended" instead.<br>
                The closing time must be a future date, and cannot be set more than 5 years ahead.
            </small>
        </div>

        <div class="extra-options__container">
            <input type="checkbox" name="is_open_ended" id="is_open_ended" value="1"
                {{ old('is_open_ended', $bet->is_open_ended ?? false) ? 'checked' : '' }}>
            <label for="is_open_ended">Open Ended</label>
        </div>

        <button type="submit">{{ $buttonText ?? 'Open' }}</button>
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
                            closingTimeInput.value = "{{ now()->addDay()->format('Y-m-d\TH:i') }}";
                        }
                    }
                }
                
                openEndedCheckbox.addEventListener('change', toggleClosingTimeRequired);
                
                // Initialize state on page load
                toggleClosingTimeRequired();

                VirtualSelect.init({
                    ele: '#min_bet_virtual_select',
                    options: [
                        {
                            label: '1,000 to 10,000 BON',
                            value: '1000'
                        },
                        {
                            label: '10,000 to 100,000 BON',
                            value: '10000'
                        },
                        {
                            label: '100,000 to 1,000,000 BON',
                            value: '100000'
                        }
                    ],
                    name: 'min_bet',
                    required: true,
                    selectedValue: "{{ old('min_bet', $bet->min_bet ?? '1000') }}"
                });
            });
        </script>
    </div>
@endsection
