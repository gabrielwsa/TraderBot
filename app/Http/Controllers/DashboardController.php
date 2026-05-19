<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Models\Position;
use App\Models\Trade;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
}
