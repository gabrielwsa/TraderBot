<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    protected $fillable = ['pair', 'signal', 'ema9', 'ema21', 'rsi', 'macd_hist', 'price', 'traded', 'skip_reason'];

    protected $casts = [
        'ema9'      => 'float',
        'ema21'     => 'float',
        'rsi'       => 'float',
        'macd_hist' => 'float',
        'price'     => 'float',
        'traded'    => 'boolean',
    ];
}
