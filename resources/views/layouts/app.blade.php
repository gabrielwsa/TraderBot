<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BotTrade' }} — Binance Bot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full text-gray-100">
    <nav class="bg-gray-900 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-green-400 text-xl font-bold">&#9650; BotTrade</span>
            <span class="text-gray-400 text-sm">Binance Spot</span>
        </div>
        <div class="flex gap-6 text-sm">
            <a href="{{ route('dashboard') }}" class="hover:text-green-400 transition {{ request()->routeIs('dashboard') ? 'text-green-400' : 'text-gray-400' }}">Dashboard</a>
            <a href="{{ route('settings') }}" class="hover:text-green-400 transition {{ request()->routeIs('settings') ? 'text-green-400' : 'text-gray-400' }}">Settings</a>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
