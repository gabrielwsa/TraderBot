<?php

use App\Jobs\MonitorPositions;
use App\Jobs\RunBotCycle;
use Illuminate\Support\Facades\Schedule;

// Monitora posições abertas a cada 15s — prioridade máxima
Schedule::job(new MonitorPositions())->everyFifteenSeconds()->withoutOverlapping();

// Escaneia novos pares a cada minuto
Schedule::job(new RunBotCycle())->everyMinute()->withoutOverlapping();
