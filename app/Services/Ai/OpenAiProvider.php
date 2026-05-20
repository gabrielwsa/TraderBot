<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProviderInterface
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gpt-4o-mini'
    ) {}

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function analyze(array $data): array
    {
        $response = Http::timeout(20)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $this->model,
                'max_tokens'  => 150,
                'temperature' => 0.1,
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a crypto day trading analyst. Always respond with valid JSON only.'],
                    ['role' => 'user',   'content' => $this->buildPrompt($data)],
                ],
            ]);

        if ($response->failed()) {
            return $this->neutral('OpenAI API error: ' . $response->status());
        }

        $content = $response->json('choices.0.message.content', '');
        return $this->parseResponse($content);
    }

    private function buildPrompt(array $data): string
    {
        return <<<PROMPT
Analyze this crypto BUY signal:
Pair: {$data['pair']} | Timeframe: {$data['timeframe']}
EMA9: {$data['ema9']} | EMA21: {$data['ema21']} | EMA50: {$data['ema50']}
RSI: {$data['rsi']} | MACD hist: {$data['macd_hist']}
BB %B: {$data['bb_pct_b']} | StochRSI K/D: {$data['stoch_k']}/{$data['stoch_d']}
ATR: {$data['atr']} | 1h Trend: {$data['trend_1h']} | Volume spike: {$data['volume_ok']}
Reasons: {$data['reasons']}

JSON response: {"decision": "CONFIRM|REJECT|NEUTRAL", "reason": "max 15 words", "confidence": 0-100}
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
