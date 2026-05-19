<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'position_id', 'pair', 'side', 'quantity', 'price',
        'total_usdt', 'fee_usdt', 'fee_asset', 'order_id', 'order_status',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'total_usdt' => 'float',
        'fee_usdt' => 'float',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
