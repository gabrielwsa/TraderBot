<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    protected $fillable = [
        'api_key', 'api_secret', 'environment', 'capital_usdt',
        'capital_per_trade_pct', 'max_open_positions', 'stop_loss_pct',
        'take_profit_pct', 'use_bnb_fees', 'min_volume_usdt', 'timeframe', 'is_active', 'pair_blacklist',
    ];

    protected $casts = [
        'capital_usdt' => 'float',
        'capital_per_trade_pct' => 'float',
        'max_open_positions' => 'integer',
        'stop_loss_pct' => 'float',
        'take_profit_pct' => 'float',
        'use_bnb_fees' => 'boolean',
        'min_volume_usdt' => 'float',
        'is_active' => 'boolean',
        'pair_blacklist' => 'array',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'environment' => 'testnet',
            'capital_usdt' => 100,
            'capital_per_trade_pct' => 10,
            'max_open_positions' => 3,
            'stop_loss_pct' => 2.00,
            'take_profit_pct' => 4.00,
            'use_bnb_fees' => false,
            'min_volume_usdt' => 1000000,
            'timeframe' => '15m',
            'is_active' => false,
        ]);
    }

    public function getFeeRateAttribute(): float
    {
        return $this->use_bnb_fees ? 0.00075 : 0.001;
    }

    public function getBaseUrlAttribute(): string
    {
        return $this->environment === 'testnet'
            ? 'https://testnet.binance.vision'
            : 'https://api.binance.com';
    }
}
