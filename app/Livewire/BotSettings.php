<?php

namespace App\Livewire;

use App\Models\BotLog;
use App\Models\BotSetting;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BotSettings extends Component
{
    public string $api_key = '';
    public string $api_secret = '';
    public string $environment = 'testnet';
    public float $capital_usdt = 100;
    public float $capital_per_trade_pct = 10;
    public int $max_open_positions = 3;
    public float $stop_loss_pct = 2.0;
    public float $take_profit_pct = 4.0;
    public bool $use_bnb_fees = false;
    public float $min_volume_usdt = 1000000;
    public string $timeframe = '15m';

    public bool $saved = false;

    public function mount(): void
    {
        $s = BotSetting::current();
        $this->api_key = $s->api_key ?? '';
        $this->api_secret = $s->api_secret ?? '';
        $this->environment = $s->environment;
        $this->capital_usdt = $s->capital_usdt;
        $this->capital_per_trade_pct = $s->capital_per_trade_pct;
        $this->max_open_positions = $s->max_open_positions;
        $this->stop_loss_pct = $s->stop_loss_pct;
        $this->take_profit_pct = $s->take_profit_pct;
        $this->use_bnb_fees = $s->use_bnb_fees;
        $this->min_volume_usdt = $s->min_volume_usdt;
        $this->timeframe = $s->timeframe;
    }

    public function save(): void
    {
        $this->validate([
            'capital_usdt'          => 'required|numeric|min:10',
            'capital_per_trade_pct' => 'required|numeric|min:1|max:100',
            'max_open_positions'    => 'required|integer|min:1|max:20',
            'stop_loss_pct'         => 'required|numeric|min:0.1|max:50',
            'take_profit_pct'       => 'required|numeric|min:0.1|max:100',
            'min_volume_usdt'       => 'required|numeric|min:0',
            'environment'           => 'required|in:testnet,production',
            'timeframe'             => 'required|in:1m,5m,15m,1h',
        ]);

        BotSetting::current()->update([
            'api_key'               => $this->api_key ?: null,
            'api_secret'            => $this->api_secret ?: null,
            'environment'           => $this->environment,
            'capital_usdt'          => $this->capital_usdt,
            'capital_per_trade_pct' => $this->capital_per_trade_pct,
            'max_open_positions'    => $this->max_open_positions,
            'stop_loss_pct'         => $this->stop_loss_pct,
            'take_profit_pct'       => $this->take_profit_pct,
            'use_bnb_fees'          => $this->use_bnb_fees,
            'min_volume_usdt'       => $this->min_volume_usdt,
            'timeframe'             => $this->timeframe,
        ]);

        BotLog::info('Settings updated');
        $this->saved = true;
        $this->dispatch('close-settings');
    }

    public function render()
    {
        return view('livewire.bot-settings');
    }
}
