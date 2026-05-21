<?php

namespace App\Jobs;

use App\Services\TradingEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonitorPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries = 1;

    public function __construct()
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        (new TradingEngine())->runMonitor();
    }
}
