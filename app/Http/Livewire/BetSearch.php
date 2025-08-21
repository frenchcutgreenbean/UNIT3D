<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Bet;
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

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingName()
    {
        $this->resetPage();
    }

    public function render()
    {
        $bets = Bet::query()
            ->with(['user', 'entries', 'outcomes'])
            ->when($this->activeTab === 'open', function($q) {
                return $q->where('status', 'open')
                        ->where(function($query) {
                            $query->where('closing_time', '>', now())
                                  ->orWhereNull('closing_time')
                                  ->orWhere('is_open_ended', true);
                        });
            })
            ->when($this->activeTab === 'closed', function($q) {
                return $q->where('status', 'closed')
                        ->orWhere(function($query) {
                            $query->where('status', 'open')
                                  ->where('closing_time', '<=', now())
                                  ->where('is_open_ended', false);
                        });
            })
            ->when($this->activeTab === 'completed', fn($q) => $q->where('status', 'completed'))
            ->when($this->name, fn($q) => $q->where('name', 'like', "%{$this->name}%"))
            ->when($this->sortField === 'activity', function($q) {
                // Custom sorting for activity field
                return $q->leftJoin('bet_entries', 'bets.id', '=', 'bet_entries.bet_id')
                        ->select('bets.*')
                        ->groupBy('bets.id')
                        ->orderByRaw('MAX(bet_entries.created_at) ' . $this->sortDirection);
            })
            ->when($this->sortField === 'pot_size', function($q) {
                // Custom sorting for pot size
                return $q->leftJoin('bet_entries', 'bets.id', '=', 'bet_entries.bet_id')
                        ->select('bets.*')
                        ->groupBy('bets.id')
                        ->orderByRaw('COALESCE(SUM(bet_entries.amount), 0) ' . $this->sortDirection);
            })
            ->when($this->sortField !== 'activity' && $this->sortField !== 'pot_size', fn($q) => $q->orderBy($this->sortField, $this->sortDirection))
            ->paginate(25);

        return view('livewire.bet-search', compact('bets'));
    }
}