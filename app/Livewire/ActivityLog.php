<?php

namespace App\Livewire;

use App\Models\BotLog;
use Livewire\Component;

class ActivityLog extends Component
{
    public function render()
    {
        $logs = BotLog::orderByDesc('created_at')->limit(50)->get();
        return view('livewire.activity-log', compact('logs'));
    }
}
