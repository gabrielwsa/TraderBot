<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements AiProviderInterface
{
    public function __construct(
        private string $baseUrl,
        private string $model
    ) {}

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    public function analyze(array $data): array
    {
        $prompt = $this->buildPrompt($data);

        $response = Http::timeout(30)->post("{$this->baseUrl}/api/generate", [
            'model'  => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
            'options' => ['temperature' => 0.1],
        ]);

        if ($response->failed()) {
            return $this->neutral('Ollama não respondeu');
        }

        return $this->parseResponse($response->json('response', ''));
    }

    private function buildPrompt(array $data): string
    {
        return <<<PROMPT
You are a crypto day trading analyst. Analyze this BUY signal and decide if it should be executed.

Pair: {$data['pair']}
Timeframe: {$data['timeframe']}

Technical Indicators:
- EMA9: {$data['ema9']} | EMA21: {$data['ema21']} | EMA50: {$data['ema50']}
- RSI(14): {$data['rsi']} (ideal: 35-65)
- MACD histogram: {$data['macd_hist']} (positive = bullish)
- Bollinger %B: {$data['bb_pct_b']} (0=lower band, 1=upper band, ideal entry: <0.7)
- Stochastic RSI K: {$data['stoch_k']} | D: {$data['stoch_d']} (ideal: K>D and K<80)
- ATR(14): {$data['atr']} (volatility)
- 1h Trend: {$data['trend_1h']} (BULL/NEUTRAL/BEAR)
- Volume spike: {$data['volume_ok']}

Signal reasons: {$data['reasons']}

Respond ONLY with valid JSON like this:
{"decision": "CONFIRM", "reason": "brief reason max 15 words", "confidence": 75}

decision must be: CONFIRM, REJECT, or NEUTRAL
confidence is 0-100
PROMPT;
    }

    private function parseResponse(string $raw): array
    {
        $raw = trim($raw);

        // Extract JSON if wrapped in text
        if (preg_match('/\{.*\}/s', $raw, $match)) {
            $raw = $match[0];
        }

        $parsed = json_decode($raw, true);

        if (!$parsed || !isset($parsed['decision'])) {
            return $this->neutral('Resposta inválida do modelo');
        }

        $decision = strtoupper($parsed['decision'] ?? 'NEUTRAL');
        if (!in_array($decision, ['CONFIRM', 'REJECT', 'NEUTRAL'])) {
            $decision = 'NEUTRAL';
        }

        return [
            'decision'   => $decision,
            'reason'     => $parsed['reason'] ?? '',
            'confidence' => (int) ($parsed['confidence'] ?? 50),
        ];
    }

    private function neutral(string $reason): array
    {
        return ['decision' => 'NEUTRAL', 'reason' => $reason, 'confidence' => 50];
    }
}
