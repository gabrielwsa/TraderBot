<?php

namespace App\Livewire;

use App\Models\Position;
use Livewire\Component;
use Livewire\WithPagination;

class TradeHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $positions = Position::where('status', 'closed')
            ->orderByDesc('updated_at')
            ->paginate(15);
        return view('livewire.trade-history', compact('positions'));
    }
}
