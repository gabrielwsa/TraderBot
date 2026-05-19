<?php
namespace App\Livewire;
use App\Models\Signal;
use Livewire\Component;
use Livewire\WithPagination;

class SignalHistory extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function render()
    {
        $query = Signal::orderByDesc('created_at');
        if ($this->filter !== 'all') {
            $query->where('signal', $this->filter);
        }
        return view('livewire.signal-history', ['signals' => $query->paginate(20)]);
    }
}
