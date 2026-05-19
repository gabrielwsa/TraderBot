<?php
namespace App\Livewire;
use App\Models\Position;
use App\Models\Trade;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TodayStats extends Component
{
    #[Computed]
    public function todayPnl(): float
    {
        return (float) Position::where('status', 'closed')
            ->whereDate('updated_at', today())
            ->sum('realized_pnl');
    }

    #[Computed]
    public function todayTrades(): int
    {
        return Position::where('status', 'closed')
            ->whereDate('updated_at', today())
            ->count();
    }

    #[Computed]
    public function todayWinRate(): float
    {
        $total = $this->todayTrades;
        if ($total === 0) return 0;
        $wins = Position::where('status', 'closed')
            ->whereDate('updated_at', today())
            ->where('realized_pnl', '>', 0)
            ->count();
        return round(($wins / $total) * 100, 1);
    }

    #[Computed]
    public function todayFees(): float
    {
        return (float) Position::where('status', 'closed')
            ->whereDate('updated_at', today())
            ->sum('total_fees_usdt');
    }

    #[Computed]
    public function todayNetPnl(): float
    {
        return $this->todayPnl;
    }

    public function render()
    {
        return view('livewire.today-stats');
    }
}
