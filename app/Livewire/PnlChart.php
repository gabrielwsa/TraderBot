<?php
namespace App\Livewire;
use App\Models\Position;
use Livewire\Component;

class PnlChart extends Component
{
    public array $chartData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $positions = Position::where('status', 'closed')
            ->orderBy('updated_at')
            ->get(['updated_at', 'realized_pnl', 'pair']);

        $cumulative = 0;
        $data = [];
        foreach ($positions as $p) {
            $cumulative += $p->realized_pnl;
            $data[] = [
                'x'    => $p->updated_at->format('d/m H:i'),
                'y'    => round($cumulative, 4),
                'pnl'  => round($p->realized_pnl, 4),
                'pair' => $p->pair,
            ];
        }

        // Add current point at 0 if no trades yet
        if (empty($data)) {
            $data[] = ['x' => now()->format('d/m H:i'), 'y' => 0, 'pnl' => 0, 'pair' => ''];
        }

        $this->chartData = $data;
        $this->dispatch('chart-data-updated', data: $data);
    }

    public function render()
    {
        return view('livewire.pnl-chart');
    }
}
