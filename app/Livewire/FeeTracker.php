<?php
namespace App\Livewire;
use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FeeTracker extends Component
{
    #[Computed]
    public function totalFees(): float
    {
        return (float) Position::sum('total_fees_usdt');
    }

    #[Computed]
    public function feesToday(): float
    {
        return (float) Position::whereDate('created_at', today())->sum('total_fees_usdt');
    }

    #[Computed]
    public function feesThisWeek(): float
    {
        return (float) Position::where('created_at', '>=', now()->startOfWeek())->sum('total_fees_usdt');
    }

    #[Computed]
    public function feesThisMonth(): float
    {
        return (float) Position::where('created_at', '>=', now()->startOfMonth())->sum('total_fees_usdt');
    }

    #[Computed]
    public function grossPnl(): float
    {
        return (float) Position::where('status', 'closed')->sum('realized_pnl');
    }

    #[Computed]
    public function feeImpactPct(): float
    {
        $gross = abs($this->grossPnl);
        if ($gross == 0) return 0;
        return round(($this->totalFees / ($gross + $this->totalFees)) * 100, 1);
    }

    public function render()
    {
        return view('livewire.fee-tracker');
    }
}
