<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }
}
