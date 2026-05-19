<?php

use App\Jobs\RunBotCycle;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new RunBotCycle())->everyMinute()->withoutOverlapping();
