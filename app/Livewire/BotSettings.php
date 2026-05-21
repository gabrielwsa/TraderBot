<?php

namespace App\Livewire;

use App\Models\BotLog;
use App\Models\BotSetting;
use App\Services\BinanceService;
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
    public float $min_volatility_pct = 1.0;
    public int $scan_limit = 50;
    public bool $trailing_stop_enabled = true;
    public float $trailing_stop_pct = 1.0;
    public string $timeframe = '15m';
    public bool $ai_enabled = false;
    public string $ai_provider = 'ollama';
    public string $ai_model = 'llama3.1';
    public string $ai_base_url = 'http://localhost:11434';
    public string $ai_api_key = '';

    public bool $saved = false;
    public array $blacklist = [];
    public string $blacklistSearch = '';
    public array $blacklistResults = [];

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
        $this->min_volatility_pct = $s->min_volatility_pct ?? 1.0;
        $this->scan_limit = $s->scan_limit ?? 50;
        $this->trailing_stop_enabled = $s->trailing_stop_enabled ?? true;
        $this->trailing_stop_pct = $s->trailing_stop_pct ?? 1.0;
        $this->timeframe = $s->timeframe;
        $this->ai_enabled  = $s->ai_enabled ?? false;
        $this->ai_provider = $s->ai_provider ?? 'ollama';
        $this->ai_model    = $s->ai_model ?? 'llama3.1';
        $this->ai_base_url = $s->ai_base_url ?? 'http://localhost:11434';
        $this->ai_api_key  = $s->ai_api_key ?? '';
        $this->blacklist = $s->pair_blacklist ?? [];
    }

    public function updatedBlacklistSearch(string $value): void
    {
        if (strlen($value) < 1) {
            $this->blacklistResults = [];
            return;
        }

        $cached = \Illuminate\Support\Facades\Cache::get('scanner_pairs', []);
        $search = strtoupper($value);

        $this->blacklistResults = collect($cached)
            ->filter(fn($p) => str_contains($p['symbol'], $search) && !in_array($p['symbol'], $this->blacklist))
            ->take(6)
            ->values()
            ->toArray();
    }

    public function addToBlacklist(string $symbol): void
    {
        if (!in_array($symbol, $this->blacklist)) {
            $this->blacklist[] = $symbol;
        }
        $this->blacklistSearch = '';
        $this->blacklistResults = [];
    }

    public function removeFromBlacklist(string $symbol): void
    {
        $this->blacklist = array_values(array_filter($this->blacklist, fn($s) => $s !== $symbol));
    }

    public function save(): void
    {
        $this->validate([
            'capital_usdt'          => 'required|numeric|min:10',
            'capital_per_trade_pct' => 'required|numeric|min:1|max:100',
            'max_open_positions'    => 'required|integer|min:1|max:20',
            'stop_loss_pct'         => 'required|numeric|min:0.01|max:50',
            'take_profit_pct'       => 'required|numeric|min:0.01|max:100',
            'min_volume_usdt'       => 'required|numeric|min:0',
            'min_volatility_pct'    => 'required|numeric|min:0|max:50',
            'scan_limit'            => 'required|integer|min:10|max:300',
            'trailing_stop_pct'     => 'required|numeric|min:0.1|max:20',
            'ai_provider'           => 'required|in:ollama,claude,openai',
            'ai_model'              => 'required|string|max:100',
            'environment'           => 'required|in:testnet,production',
            'timeframe'             => 'required|in:1m,5m,15m,1h',
        ]);

        // Validate capital against real balance when API keys are provided
        if (!empty($this->api_key) && !empty($this->api_secret)) {
            try {
                $tempSettings = BotSetting::current();
                $tempSettings->api_key = $this->api_key;
                $tempSettings->api_secret = $this->api_secret;
                $tempSettings->environment = $this->environment;

                $wallet  = (new BinanceService($tempSettings))->getWalletInfo();
                $balance = collect($wallet['balances'])
                    ->whereIn('asset', ['USDT', 'USD'])
                    ->sum('free');

                if ($this->capital_usdt > $balance) {
                    $this->addError('capital_usdt', "Capital maior que o saldo disponível ({$balance} USDT).");
                    return;
                }
            } catch (\Exception $e) {
                // If we can't fetch the balance, skip this validation
            }
        }

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
            'min_volatility_pct'    => $this->min_volatility_pct,
            'scan_limit'            => $this->scan_limit,
            'trailing_stop_enabled' => $this->trailing_stop_enabled,
            'trailing_stop_pct'     => $this->trailing_stop_pct,
            'timeframe'             => $this->timeframe,
            'ai_enabled'            => $this->ai_enabled,
            'ai_provider'           => $this->ai_provider,
            'ai_model'              => $this->ai_model,
            'ai_base_url'           => $this->ai_base_url ?: 'http://localhost:11434',
            'ai_api_key'            => $this->ai_api_key ?: null,
            'pair_blacklist'        => $this->blacklist ?: null,
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
