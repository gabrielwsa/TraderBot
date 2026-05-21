<?php

namespace App\Services;

use App\Contracts\AiProviderInterface;
use App\Models\BotSetting;
use App\Services\Ai\ClaudeProvider;
use App\Services\Ai\OllamaProvider;
use App\Services\Ai\OpenAiProvider;

class AiAnalysisService
{
    private ?AiProviderInterface $provider;

    public function __construct(BotSetting $settings)
    {
        $this->provider = $settings->ai_enabled ? $this->buildProvider($settings) : null;
    }

    private function buildProvider(BotSetting $settings): AiProviderInterface
    {
        return match ($settings->ai_provider) {
            'claude' => new ClaudeProvider(
                $settings->ai_api_key ?? '',
                $settings->ai_model ?: 'claude-haiku-4-5-20251001'
            ),
            'openai' => new OpenAiProvider(
                $settings->ai_api_key ?? '',
                $settings->ai_model ?: 'gpt-4o-mini'
            ),
            default  => new OllamaProvider(
                $settings->ai_base_url ?: 'http://localhost:11434',
                $settings->ai_model ?: 'llama3.1'
            ),
        };
    }

    public function isEnabled(): bool
    {
        return $this->provider !== null;
    }

    /**
     * Validate a BUY signal. Returns true if trade should proceed.
     * CONFIRM or NEUTRAL → proceed. REJECT → skip.
     */
    public function validateSignal(string $pair, array $analysis, string $trend1h, string $timeframe, array $context = []): array
    {
        if (!$this->provider) {
            return ['proceed' => true, 'decision' => 'DISABLED', 'reason' => '', 'confidence' => 100];
        }

        $fundingRate = $context['funding_rate'] ?? null;

        try {
            $result = $this->provider->analyze([
                'pair'             => $pair,
                'timeframe'        => $timeframe,
                'ema9'             => round($analysis['ema9'], 6),
                'ema21'            => round($analysis['ema21'], 6),
                'ema50'            => round($analysis['ema50'] ?? 0, 6),
                'rsi'              => $analysis['rsi'],
                'macd_hist'        => $analysis['macd_hist'],
                'bb_pct_b'         => $analysis['bb_pct_b'] ?? 0.5,
                'stoch_k'          => $analysis['stoch_k'] ?? 50,
                'stoch_d'          => $analysis['stoch_d'] ?? 50,
                'atr'              => round($analysis['atr'] ?? 0, 8),
                'trend_1h'         => $trend1h,
                'volume_ok'        => $analysis['volume_ok'] ? 'yes' : 'no',
                'reasons'          => implode(', ', $analysis['reasons'] ?? []),
                'btc_trend_1h'     => $context['btc_trend_1h'] ?? 'NEUTRAL',
                'fear_greed_value' => $context['fear_greed_value'] ?? 50,
                'fear_greed_label' => $context['fear_greed_label'] ?? 'Neutral',
                'orderbook_ratio'  => $context['orderbook_ratio'] ?? 1.0,
                'funding_rate'     => $fundingRate !== null ? number_format($fundingRate * 100, 4) . '%' : 'N/A',
                'price_change_24h' => ($context['price_change_24h'] ?? 0.0) . '%',
            ]);

            return [
                'proceed'    => $result['decision'] !== 'REJECT',
                'decision'   => $result['decision'],
                'reason'     => $result['reason'],
                'confidence' => $result['confidence'],
            ];
        } catch (\Exception $e) {
            // AI failure never blocks a trade — fall through
            return ['proceed' => true, 'decision' => 'ERROR', 'reason' => $e->getMessage(), 'confidence' => 50];
        }
    }
}
