<?php

namespace App\Livewire;

use App\Models\BotLog;
use App\Models\BotSetting;
use App\Models\Position;
use App\Models\Trade;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BotStatus extends Component
{
    public ?string $error = null;

    public function toggle(): void
    {
        $settings = BotSetting::current();

        if (!$settings->is_active) {
            if (empty($settings->api_key) || empty($settings->api_secret)) {
                $this->error = 'Configure as chaves de API antes de iniciar o bot.';
                return;
            }
        }

        $this->error = null;
        $settings->update(['is_active' => !$settings->is_active]);
        BotLog::info($settings->is_active ? 'Bot iniciado pelo usuário' : 'Bot parado pelo usuário');
    }

    #[Computed]
    public function settings(): BotSetting
    {
        return BotSetting::current();
    }

    #[Computed]
    public function totalPnl(): float
    {
        return (float) Position::where('status', 'closed')->sum('realized_pnl');
    }

    #[Computed]
    public function winRate(): float
    {
        $closed = Position::where('status', 'closed')->count();
        if ($closed === 0) return 0;
        $wins = Position::where('status', 'closed')->where('realized_pnl', '>', 0)->count();
        return round(($wins / $closed) * 100, 1);
    }

    #[Computed]
    public function totalTrades(): int
    {
        return Position::where('status', 'closed')->count();
    }

    #[Computed]
    public function openPositions(): int
    {
        return Position::open()->count();
    }

    #[Computed]
    public function lastActivity(): ?string
    {
        $log = BotLog::whereIn('level', ['info', 'trade'])
            ->orderByDesc('created_at')
            ->first();

        return $log ? $log->created_at->diffForHumans() : null;
    }

    public function render()
    {
        return view('livewire.bot-status');
    }
}
