<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'pair', 'entry_price', 'quantity', 'invested_usdt', 'current_price',
        'unrealized_pnl', 'unrealized_pnl_pct', 'stop_loss_price', 'take_profit_price',
        'status', 'close_price', 'realized_pnl', 'realized_pnl_pct',
        'total_fees_usdt', 'close_reason', 'buy_order_id', 'sell_order_id',
    ];

    protected $casts = [
        'entry_price' => 'float',
        'quantity' => 'float',
        'invested_usdt' => 'float',
        'current_price' => 'float',
        'unrealized_pnl' => 'float',
        'unrealized_pnl_pct' => 'float',
        'stop_loss_price' => 'float',
        'take_profit_price' => 'float',
        'close_price' => 'float',
        'realized_pnl' => 'float',
        'realized_pnl_pct' => 'float',
        'total_fees_usdt' => 'float',
    ];

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
