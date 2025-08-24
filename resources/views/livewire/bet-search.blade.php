<div class="bet__index-container">
    <div class="bet__header-container">
        <h1 class="bet__header-title">Bets
            @if (can_create_bet($user))
                | <a href="/bets/create">New</a>
            @endif
        </h1>
        
    </div>
        <div class="bet__sub-nav-container">
            <ul class="bet__sub-nav">
                <li class="bet__sub-nav-item {{ $activeTab === 'open' ? 'active' : '' }}" wire:click="setTab('open')">Open
                </li>
                <li class="bet__sub-nav-item {{ $activeTab === 'closed' ? 'active' : '' }}" wire:click="setTab('closed')">
                    Closed</li>
                <li class="bet__sub-nav-item {{ $activeTab === 'completed' ? 'active' : '' }}"
                    wire:click="setTab('completed')">Completed</li>
                <li class="bet__sub-nav-item {{ $activeTab === 'cancelled' ? 'active' : '' }}"
                    wire:click="setTab('cancelled')">Cancelled</li>
            </ul>
        </div>
    <search class="bet__compact-search bet__search-filters" x-data="toggle">
        <div class="bet__compact-search-visible-default">
            <p class="form__group">
                <input id="name" wire:model.live="name" type="search" autocomplete="off" class="form__text"
                    placeholder=" " />
                <label class="form__label form__label--floating" for="name">
                    {{ __('common.search') }}
                </label>
            </p>
        </div>
    </search>
    <div class="bet__tab-content">
        <table>
            <thead>
                <tr>
                    <th class="bet__info-head"
                        wire:click="sortBy('name')" role="columnheader button">
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
                    <th wire:click="sortBy('total_entries')" role="columnheader button">
                        Members
                        @include('livewire.includes._sort-icon', ['field' => 'total_entries'])
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($bets as $bet)
                    <tr class="{{ $bet->status === \App\Enums\BetStatus::CANCELLED ? 'bet--cancelled' : '' }}">
                        <td class="bet__info">
                            <a href="/bets/{{ $bet->id }}" class="bet__link"> {{ $bet->name }}</a>
                            <x-user-tag
                                    :user="$bet->user"
                                    :anon="$bet->anon"
                                />
                            <p class="bet__range"><i class="fas fa-coins"></i>{{ $bet->min_bet }} - {{ $bet->min_bet * 10 }}</p>
                        </td>
                        <td class="bet__activity">{{ $bet->activity ? $bet->activity : 'N/A' }}</td>
                        <td class="bet__closing-time">
                            <time datetime="{{ $bet->closing_time }}" title="{{ $bet->closing_time }}">
                                {{ $bet->closing_time ? $bet->closing_time->diffForHumans() : 'N/A' }}
                            </time>
                        </td>
                        <td class="bet__created-at">
                            <time datetime="{{ $bet->created_at }}" title="{{ $bet->created_at }}">
                                {{ $bet->created_at ? $bet->created_at->diffForHumans() : 'N/A' }}
                            </time>
                        </td>
                        <td class="bet__pot-size">
                            <i class="fas fa-coins"></i>
                            {{ $bet->pot_size }}
                        </td>
                        <td class="bet__total-members">{{ $bet->total_entries }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No bets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $bets->links('partials.pagination') }}
        </div>
    </div>
</div>
