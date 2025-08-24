<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Bet;
use App\Enums\BetStatus;
use App\Traits\LivewireSort;
use Livewire\Attributes\Url;

class BetSearch extends Component
{
    use WithPagination;
    use LivewireSort;

    #[Url(history: true)]
    public $activeTab = 'open';
    
    #[Url(history: true)]
    public $name = '';
    
    #[Url(history: true)]
    public string $sortField = 'created_at';
    
    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public int $perPage = 10;

    public function mount()
    {
        $this->perPage = config('betting.items_per_page', 10);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingName()
    {
        $this->resetPage();
    }

    public function updatedSortField()
    {
        $this->resetPage();
    }

    public function updatedSortDirection()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function render()
    {
        $user = auth()->user();

        $bets = Bet::query()
            ->with(['user', 'entries', 'outcomes'])
            ->withCount(['entries as total_entries'])
            ->when($this->activeTab === 'open', function($q) {
                return $q->where('status', BetStatus::OPEN)
                        ->where(function($query) {
                            $query->where('closing_time', '>', now())
                                  ->orWhereNull('closing_time')
                                  ->orWhere('is_open_ended', true);
                        });
            })
            ->when($this->activeTab === 'closed', function($q) {
                return $q->where('status', BetStatus::CLOSED)
                        ->orWhere(function($query) {
                            $query->where('status', BetStatus::OPEN)
                                  ->where('closing_time', '<=', now())
                                  ->where('is_open_ended', false);
                        });
            })
            ->when($this->activeTab === 'completed', fn($q) => $q->where('status', BetStatus::COMPLETED))
            ->when($this->activeTab === 'cancelled', fn($q) => $q->where('status', BetStatus::CANCELLED))
            ->when($this->name, fn($q) => $q->where('name', 'like', "%{$this->name}%"))

            ->when($this->sortField === 'activity', function($q) {
                // leave out select('bets.*') so withCount() stays in the select list
                return $q->leftJoin('bet_entries', 'bets.id', '=', 'bet_entries.bet_id')
                         ->groupBy('bets.id')
                         ->orderByRaw('(MAX(bet_entries.created_at) IS NULL), MAX(bet_entries.created_at) ' . $this->sortDirection);
            })
            ->when($this->sortField === 'pot_size', function($q) {
                return $q->leftJoin('bet_entries', 'bets.id', '=', 'bet_entries.bet_id')
                         ->groupBy('bets.id')
                         ->orderByRaw('(SUM(bet_entries.amount) IS NULL), COALESCE(SUM(bet_entries.amount), 0) ' . $this->sortDirection);
            })
            ->when($this->sortField === 'closing_time', function($q) {
                return $q->orderByRaw('(closing_time IS NULL), closing_time ' . $this->sortDirection);
            })
            // sort by the DB-side alias we added above
            ->when($this->sortField === 'total_entries', fn($q) => $q->orderBy('total_entries', $this->sortDirection))

            ->when($this->sortField !== 'activity' 
                   && $this->sortField !== 'pot_size' 
                   && $this->sortField !== 'closing_time'
                   && $this->sortField !== 'total_entries',
                  fn($q) => $q->orderBy($this->sortField, $this->sortDirection))
            ->paginate($this->perPage);

        // safe numeric start index for numbering, avoid doing arithmetic with the paginator itself
        $startIndex = ($bets->currentPage() - 1) * $bets->perPage();

        return view('livewire.bet-search', compact('bets', 'user', 'startIndex'));
    }
}