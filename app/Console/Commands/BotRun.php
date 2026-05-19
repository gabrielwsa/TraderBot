<?php

namespace App\Console\Commands;

use App\Services\TradingEngine;
use Illuminate\Console\Command;

class BotRun extends Command
{
    protected $signature = 'bot:run';
    protected $description = 'Run one bot cycle manually';

    public function handle(): void
    {
        $this->info('Running bot cycle...');
        (new TradingEngine())->runCycle();
        $this->info('Done.');
    }
}
