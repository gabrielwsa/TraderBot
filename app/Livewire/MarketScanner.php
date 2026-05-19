<?php
namespace App\Livewire;
use App\Models\BotSetting;
use App\Services\BinanceService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MarketScanner extends Component
{
    public array $pairs = [];
    public ?string $lastUpdated = null;
    public bool $loading = false;

    public function mount(): void
    {
        $this->loadFromCache();
    }

    public function refresh(): void
    {
        $this->loading = true;
        $settings = BotSetting::current();

        // Public endpoint — no API key needed
        $baseUrl = $settings->base_url;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("{$baseUrl}/api/v3/ticker/24hr");

            if ($response->failed()) {
                $this->loading = false;
                return;
            }

            $pairs = collect($response->json())
                ->filter(fn($t) =>
                    str_ends_with($t['symbol'], 'USDT') &&
                    !in_array($t['symbol'], ['USDTUSDT', 'BUSDUSDT', 'TUSDUSDT', 'USDCUSDT']) &&
                    (float) $t['quoteVolume'] >= $settings->min_volume_usdt
                )
                ->sortByDesc('quoteVolume')
                ->take(20)
                ->map(fn($t) => [
                    'symbol'       => $t['symbol'],
                    'price'        => (float) $t['lastPrice'],
                    'change_pct'   => round((float) $t['priceChangePercent'], 2),
                    'volume_usdt'  => round((float) $t['quoteVolume'] / 1_000_000, 1),
                ])
                ->values()
                ->toArray();

            Cache::put('scanner_pairs', $pairs, 60);
            $this->pairs = $pairs;
            $this->lastUpdated = now()->format('H:i:s');
        } catch (\Exception $e) {
            // silently fail
        }

        $this->loading = false;
    }

    private function loadFromCache(): void
    {
        $cached = Cache::get('scanner_pairs');
        if ($cached) {
            $this->pairs = $cached;
            $this->lastUpdated = 'cache';
        }
    }

    public function render()
    {
        return view('livewire.market-scanner');
    }
}
