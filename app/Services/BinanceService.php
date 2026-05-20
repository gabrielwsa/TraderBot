<?php

namespace App\Services;

use App\Models\BotSetting;
use Illuminate\Support\Facades\Http;
use Exception;

class BinanceService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;

    public function __construct(BotSetting $settings)
    {
        $this->baseUrl = $settings->base_url;
        $this->apiKey = $settings->api_key ?? '';
        $this->apiSecret = $settings->api_secret ?? '';
    }

    public function getKlines(string $symbol, string $interval, int $limit = 100): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/api/v3/klines", [
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => $limit,
        ]);

        $this->assertSuccess($response);

        return array_map(fn($k) => [
            'open_time' => $k[0],
            'open'      => (float) $k[1],
            'high'      => (float) $k[2],
            'low'       => (float) $k[3],
            'close'     => (float) $k[4],
            'volume'    => (float) $k[5],
        ], $response->json());
    }

    public function getTopUsdtPairs(float $minVolume, int $limit = 30, float $minVolatilityPct = 0): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/api/v3/ticker/24hr");
        $this->assertSuccess($response);

        return collect($response->json())
            ->filter(fn($t) =>
                str_ends_with($t['symbol'], 'USDT') &&
                !in_array($t['symbol'], ['USDTUSDT', 'BUSDUSDT', 'TUSDUSDT', 'USDCUSDT']) &&
                (float) $t['quoteVolume'] >= $minVolume &&
                (float) $t['lastPrice'] > 0 &&
                abs((float) $t['priceChangePercent']) >= $minVolatilityPct
            )
            ->sortByDesc('quoteVolume')
            ->take($limit)
            ->pluck('symbol')
            ->values()
            ->toArray();
    }

    public function getPrice(string $symbol): float
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/api/v3/ticker/price", ['symbol' => $symbol]);
        $this->assertSuccess($response);
        return (float) $response->json('price');
    }

    public function getUsdtBalance(): float
    {
        $data = $this->signedGet('/api/v3/account');
        $balances = collect($data['balances'] ?? []);
        $usdt = $balances->firstWhere('asset', 'USDT');
        return $usdt ? (float) $usdt['free'] : 0.0;
    }

    public function getWalletInfo(): array
    {
        $data = $this->signedGet('/api/v3/account');

        $balances = collect($data['balances'] ?? [])
            ->filter(fn($b) => (float) $b['free'] > 0 || (float) $b['locked'] > 0)
            ->map(fn($b) => [
                'asset'  => $b['asset'],
                'free'   => (float) $b['free'],
                'locked' => (float) $b['locked'],
                'total'  => (float) $b['free'] + (float) $b['locked'],
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();

        return [
            'balances'         => $balances,
            'can_trade'        => (bool) ($data['canTrade'] ?? false),
            'maker_commission' => ($data['makerCommission'] ?? 10) / 100,
            'taker_commission' => ($data['takerCommission'] ?? 10) / 100,
            'account_type'     => $data['accountType'] ?? 'SPOT',
        ];
    }

    public function testConnection(): array
    {
        try {
            $ping = Http::timeout(5)->get("{$this->baseUrl}/api/v3/ping");
            if ($ping->failed()) {
                return ['ok' => false, 'message' => 'Servidor inacessível'];
            }

            if (empty($this->apiKey) || empty($this->apiSecret)) {
                return ['ok' => false, 'message' => 'Chaves de API não configuradas'];
            }

            $this->signedGet('/api/v3/account');
            return ['ok' => true, 'message' => 'Conectado com sucesso'];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['ok' => false, 'message' => 'Timeout — API não respondeu em 10s'];
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '-2014') || str_contains($msg, 'API-key')) {
                return ['ok' => false, 'message' => 'API Key inválida'];
            }
            if (str_contains($msg, '-1022') || str_contains($msg, 'Signature')) {
                return ['ok' => false, 'message' => 'API Secret inválido'];
            }
            return ['ok' => false, 'message' => $msg];
        }
    }

    public function getLotSizeFilter(string $symbol): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/api/v3/exchangeInfo", ['symbol' => $symbol]);
        $this->assertSuccess($response);

        $filters = collect($response->json('symbols.0.filters') ?? []);
        $lotSize = $filters->firstWhere('filterType', 'LOT_SIZE');

        return [
            'min_qty'   => (float) ($lotSize['minQty'] ?? 0.00001),
            'step_size' => (float) ($lotSize['stepSize'] ?? 0.00001),
        ];
    }

    public function placeBuyOrder(string $symbol, float $quoteQty): array
    {
        return $this->signedPost('/api/v3/order', [
            'symbol'        => $symbol,
            'side'          => 'BUY',
            'type'          => 'MARKET',
            'quoteOrderQty' => number_format($quoteQty, 2, '.', ''),
        ]);
    }

    public function placeSellOrder(string $symbol, float $quantity): array
    {
        $lotSize = $this->getLotSizeFilter($symbol);
        $quantity = $this->adjustQuantity($quantity, $lotSize['step_size']);

        return $this->signedPost('/api/v3/order', [
            'symbol'   => $symbol,
            'side'     => 'SELL',
            'type'     => 'MARKET',
            'quantity' => number_format($quantity, 8, '.', ''),
        ]);
    }

    private function adjustQuantity(float $qty, float $stepSize): float
    {
        if ($stepSize <= 0) return $qty;
        return floor($qty / $stepSize) * $stepSize;
    }

    private function signedGet(string $path, array $params = []): array
    {
        $params['timestamp'] = (int) (microtime(true) * 1000);
        $query = http_build_query($params);
        $params['signature'] = hash_hmac('sha256', $query, $this->apiSecret);

        $response = Http::timeout(10)->withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->get("{$this->baseUrl}{$path}", $params);

        $this->assertSuccess($response);
        return $response->json();
    }

    private function signedPost(string $path, array $params = []): array
    {
        $params['timestamp'] = (int) (microtime(true) * 1000);
        $query = http_build_query($params);
        $params['signature'] = hash_hmac('sha256', $query, $this->apiSecret);

        $response = Http::timeout(10)->withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->asForm()
            ->post("{$this->baseUrl}{$path}", $params);

        $this->assertSuccess($response);
        return $response->json();
    }

    private function assertSuccess($response): void
    {
        if ($response->failed()) {
            $msg = $response->json('msg') ?? $response->body();
            throw new Exception("Binance API error: {$msg} (HTTP {$response->status()})");
        }
    }
}
