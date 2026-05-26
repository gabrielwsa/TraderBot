<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    protected $fillable = [
        'api_key', 'api_secret', 'environment', 'capital_usdt',
        'capital_per_trade_pct', 'max_open_positions', 'stop_loss_pct',
        'take_profit_pct', 'stop_loss_enabled', 'trailing_stop_enabled', 'trailing_stop_pct',
        'use_bnb_fees', 'min_volume_usdt', 'min_volatility_pct', 'scan_limit', 'timeframe', 'is_active', 'pair_blacklist',
        'ai_enabled', 'ai_provider', 'ai_model', 'ai_base_url', 'ai_api_key',
    ];

    protected $casts = [
        'capital_usdt' => 'float',
        'capital_per_trade_pct' => 'float',
        'max_open_positions' => 'integer',
        'stop_loss_pct' => 'float',
        'take_profit_pct' => 'float',
        'stop_loss_enabled'  => 'boolean',
        'trailing_stop_enabled' => 'boolean',
        'trailing_stop_pct' => 'float',
        'use_bnb_fees' => 'boolean',
        'min_volume_usdt' => 'float',
        'min_volatility_pct' => 'float',
        'scan_limit' => 'integer',
        'is_active' => 'boolean',
        'pair_blacklist' => 'array',
        'ai_enabled'    => 'boolean',
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
            'stop_loss_enabled'  => true,
            'trailing_stop_enabled' => true,
            'trailing_stop_pct' => 1.0,
            'use_bnb_fees' => false,
            'min_volume_usdt' => 1000000,
            'min_volatility_pct' => 1.0,
            'scan_limit' => 50,
            'timeframe' => '15m',
            'is_active'    => false,
            'ai_enabled'   => false,
            'ai_provider'  => 'ollama',
            'ai_model'     => 'llama3.1',
            'ai_base_url'  => 'http://localhost:11434',
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
