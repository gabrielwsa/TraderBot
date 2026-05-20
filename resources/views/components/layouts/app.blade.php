<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BotTrade' }} — Binance Bot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { gray: { 950: '#07080d' } },
                    animation: {
                        'ping-slow': 'ping 2s cubic-bezier(0,0,0.2,1) infinite',
                        'scan': 'scan 3s ease-in-out infinite',
                    },
                    keyframes: {
                        scan: {
                            '0%,100%': { opacity: '0.4' },
                            '50%': { opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireStyles
</head>
<body class="bg-gray-950 text-gray-100 min-h-full" x-data="{ dropdownOpen: false, settingsOpen: false }"
      @close-settings.window="settingsOpen = false">

    {{-- Navbar --}}
    <nav class="bg-gray-900 border-b border-gray-700 px-5 py-3 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <span class="text-green-400 text-lg font-bold tracking-tight">&#9650; BotTrade</span>
            <span class="text-gray-400 text-xs">Binance Spot</span>
        </div>
        <div class="flex items-center gap-1">
            <a href="{{ route('dashboard') }}"
               class="px-3 py-1.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                Dashboard
            </a>

            {{-- User dropdown --}}
            <div class="relative ml-2">
                <button @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 transition">
                    <span class="w-6 h-6 rounded-full bg-green-700 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span>{{ auth()->user()->name }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400 transition" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="dropdownOpen" @click.outside="dropdownOpen = false" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-1 w-48 bg-gray-800 border border-gray-700 rounded-xl shadow-xl py-1 z-50">
                    <button @click="settingsOpen = true; dropdownOpen = false"
                            class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Configurações
                    </button>
                    <div class="border-t border-gray-700 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-400 hover:bg-gray-700 hover:text-red-400 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Settings Modal --}}
    <div x-show="settingsOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="settingsOpen = false"></div>
        <div class="relative bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
                <h2 class="text-white font-semibold">Configurações do Bot</h2>
                <button @click="settingsOpen = false" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <livewire:bot-settings />
            </div>
        </div>
    </div>

    <main class="px-4 py-5">
        {{ $slot }}
    </main>

    @livewireScripts
    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
