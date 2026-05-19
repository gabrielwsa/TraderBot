<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotLog extends Model
{
    protected $fillable = ['level', 'message', 'context'];

    protected $casts = [
        'context' => 'array',
    ];

    public static function info(string $message, array $context = []): void
    {
        self::create(['level' => 'info', 'message' => $message, 'context' => $context ?: null]);
    }

    public static function trade(string $message, array $context = []): void
    {
        self::create(['level' => 'trade', 'message' => $message, 'context' => $context ?: null]);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::create(['level' => 'warning', 'message' => $message, 'context' => $context ?: null]);
    }

    public static function error(string $message, array $context = []): void
    {
        self::create(['level' => 'error', 'message' => $message, 'context' => $context ?: null]);
    }
}
