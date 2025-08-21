<div class="page__bets">
    <div class="header__container">
        <h1 class="header__title">Bets |</h1>
        <a href="/bets/create">Create New</a>
    </div>
    <div class="alpha-table">
        <div class="sub-nav__container">
            <ul class="sub-nav">
                <li class="sub-nav__item {{ $activeTab === 'open' ? 'active' : '' }}" wire:click="setTab('open')">Open
                </li>
                <li class="sub-nav__item {{ $activeTab === 'closed' ? 'active' : '' }}" wire:click="setTab('closed')">
                    Closed</li>
                <li class="sub-nav__item {{ $activeTab === 'completed' ? 'active' : '' }}"
                    wire:click="setTab('completed')">Completed</li>
            </ul>
        </div>
    </div>
    <search class="compact-search bet-search__filters" x-data="toggle">
        <div class="compact-search__visible-default">
            <p class="form__group">
                <input id="name" wire:model.live="name" type="search" autocomplete="off" class="form__text"
                    placeholder=" " />
                <label class="form__label form__label--floating" for="name">
                    {{ __('common.search') }}
                </label>
            </p>
        </div>
    </search>
    <div class="bet-tab-content">
        <table>
            <thead>
                <tr>
                    <th wire:click="sortBy('name')" role="columnheader button">
                        {{ __('common.name') }}
                        @include('livewire.includes._sort-icon', ['field' => 'name'])
                    </th>
                    <th wire:click="sortBy('activity')" role="columnheader button">
                        Activity
                        @include('livewire.includes._sort-icon', ['field' => 'activity'])
                    </th>
                    <th wire:click="sortBy('closing_time')" role="columnheader button">
                        Closing Time
                        @include('livewire.includes._sort-icon', ['field' => 'closing_time'])
                    </th>
                    <th wire:click="sortBy('created_at')" role="columnheader button">
                        Created
                        @include('livewire.includes._sort-icon', ['field' => 'created_at'])
                    </th>
                    <th wire:click="sortBy('pot_size')" role="columnheader button">
                        Pot Size
                        @include('livewire.includes._sort-icon', ['field' => 'pot_size'])
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($bets as $bet)
                    <tr>
                        <td>
                            <a href="/bets/{{ $bet->id }}" class="bet-link"> {{ $bet->name }}</a>
                            <x-user-tag
                                    :user="$bet->user"
                                    :anon="$bet->anon"
                                />
                            <p class="bet-range"><i class="fas fa-coins"></i>{{ $bet->min_bet }} - {{ $bet->min_bet * 10 }}</p>
                        </td>
                        <td>{{ $bet->activity }}</td>
                        <td>
                            <time datetime="{{ $bet->closing_time }}" title="{{ $bet->closing_time }}">
                                {{ $bet->closing_time ? $bet->closing_time->diffForHumans() : 'N/A' }}
                            </time>
                        </td>
                        <td>
                            <time datetime="{{ $bet->created_at }}" title="{{ $bet->created_at }}">
                                {{ $bet->created_at ? $bet->created_at->diffForHumans() : 'N/A' }}
                            </time>
                        </td>
                        <td class="bet__pot-size">
                            <i class="fas fa-coins"></i>
                            {{ $bet->pot_size }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No bets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
