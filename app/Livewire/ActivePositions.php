<?php

namespace App\Livewire;

use App\Models\Position;
use App\Services\BinanceService;
use App\Models\BotSetting;
use Livewire\Component;

class ActivePositions extends Component
{
    public function render()
    {
        $positions = Position::open()->orderByDesc('created_at')->get();
        return view('livewire.active-positions', compact('positions'));
    }
}
