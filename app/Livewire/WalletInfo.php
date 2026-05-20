<?php

namespace App\Livewire;

use App\Models\BotSetting;
use App\Services\BinanceService;
use Livewire\Component;

class WalletInfo extends Component
{
    public bool $connected = false;
    public string $connectionMessage = 'Aguardando...';
    public array $balances = [];
    public bool $canTrade = false;
    public float $makerFee = 0;
    public float $takerFee = 0;
    public string $accountType = '';
    public ?string $lastChecked = null;
    public bool $checking = false;
    public bool $hasApiKeys = false;
    public float $usdtBalance = 0;
    public bool $lowUsdtWarning = false;

    public function mount(): void
    {
        $settings = BotSetting::current();
        $this->hasApiKeys = !empty($settings->api_key) && !empty($settings->api_secret);

        if ($this->hasApiKeys) {
            $this->check();
        }
    }

    public function check(): void
    {
        $this->checking = true;
        $settings = BotSetting::current();
        $this->hasApiKeys = !empty($settings->api_key) && !empty($settings->api_secret);

        if (!$this->hasApiKeys) {
            $this->connected = false;
            $this->connectionMessage = 'Chaves de API não configuradas';
            $this->checking = false;
            return;
        }

        $binance = new BinanceService($settings);
        $test = $binance->testConnection();

        $this->connected = $test['ok'];
        $this->connectionMessage = $test['message'];

        if ($test['ok']) {
            try {
                $wallet = $binance->getWalletInfo();
                $this->balances = $wallet['balances'];
                $this->canTrade = $wallet['can_trade'];
                $this->makerFee = $wallet['maker_commission'];
                $this->takerFee = $wallet['taker_commission'];
                $this->accountType = $wallet['account_type'];

                $this->usdtBalance = collect($this->balances)
                    ->whereIn('asset', ['USDT', 'USD'])
                    ->sum('free');
                $this->lowUsdtWarning = $this->usdtBalance < 10;
            } catch (\Exception $e) {
                $this->connectionMessage = $e->getMessage();
                $this->connected = false;
            }
        }

        $this->lastChecked = now()->format('H:i:s');
        $this->checking = false;
    }

    public function render()
    {
        return view('livewire.wallet-info');
    }
}
