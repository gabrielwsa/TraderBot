<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use Illuminate\Http\JsonResponse;

class BotController extends Controller
{
    public function toggle(): JsonResponse
    {
        $settings = BotSetting::current();
        $settings->update(['is_active' => !$settings->is_active]);

        return response()->json([
            'is_active' => $settings->is_active,
            'message' => $settings->is_active ? 'Bot started' : 'Bot stopped',
        ]);
    }
}
