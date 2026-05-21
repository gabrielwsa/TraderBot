<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class ClaudeProvider implements AiProviderInterface
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct(
        private string $apiKey,
        private string $model = 'claude-haiku-4-5-20251001'
    ) {}

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function analyze(array $data): array
    {
        $response = Http::timeout(20)
            ->withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])
            ->post(self::API_URL, [
                'model'      => $this->model,
                'max_tokens' => 150,
                'messages'   => [
                    ['role' => 'user', 'content' => $this->buildPrompt($data)],
                ],
            ]);

        if ($response->failed()) {
            return $this->neutral('Claude API error: ' . $response->status());
        }

        $content = $response->json('content.0.text', '');
        return $this->parseResponse($content);
    }

    private function buildPrompt(array $data): string
    {
        return <<<PROMPT
You are a crypto day trading analyst. Analyze this BUY signal and decide if it should be executed.

Pair: {$data['pair']} | Timeframe: {$data['timeframe']}
EMA9: {$data['ema9']} | EMA21: {$data['ema21']} | EMA50: {$data['ema50']}
RSI: {$data['rsi']} | MACD hist: {$data['macd_hist']}
BB %B: {$data['bb_pct_b']} | StochRSI K/D: {$data['stoch_k']}/{$data['stoch_d']}
ATR: {$data['atr']} | 1h Trend: {$data['trend_1h']} | Volume spike: {$data['volume_ok']}
Signal reasons: {$data['reasons']}

Market Context:
BTC 1h trend: {$data['btc_trend_1h']} | Fear & Greed: {$data['fear_greed_value']}/100 ({$data['fear_greed_label']})
Orderbook ratio: {$data['orderbook_ratio']} (>1=buy pressure) | Funding rate: {$data['funding_rate']}
24h price change: {$data['price_change_24h']}

Respond ONLY with JSON: {"decision": "CONFIRM|REJECT|NEUTRAL", "reason": "max 15 words", "confidence": 0-100}
PROMPT;
    }

    private function parseResponse(string $raw): array
    {
        if (preg_match('/\{.*\}/s', $raw, $match)) {
            $parsed = json_decode($match[0], true);
            if ($parsed && isset($parsed['decision'])) {
                $decision = strtoupper($parsed['decision']);
                if (!in_array($decision, ['CONFIRM', 'REJECT', 'NEUTRAL'])) {
                    $decision = 'NEUTRAL';
                }
                return [
                    'decision'   => $decision,
                    'reason'     => $parsed['reason'] ?? '',
                    'confidence' => (int) ($parsed['confidence'] ?? 50),
                ];
            }
        }
        return $this->neutral('Resposta inválida');
    }

    private function neutral(string $reason): array
    {
        return ['decision' => 'NEUTRAL', 'reason' => $reason, 'confidence' => 50];
    }
}
