<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketSentimentService
{
    public function getFearAndGreed(): array
    {
        return Cache::remember('fear_greed_index', 1800, function () {
            try {
                $response = Http::timeout(5)->get('https://api.alternative.me/fng/');
                if ($response->failed()) {
                    return ['value' => 50, 'label' => 'Neutral', 'available' => false];
                }
                $item = $response->json('data.0', []);
                return [
                    'value'     => (int) ($item['value'] ?? 50),
                    'label'     => $item['value_classification'] ?? 'Neutral',
                    'available' => true,
                ];
            } catch (\Exception) {
                return ['value' => 50, 'label' => 'Neutral', 'available' => false];
            }
        });
    }
}
