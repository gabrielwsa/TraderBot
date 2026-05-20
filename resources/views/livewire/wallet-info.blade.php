<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden h-full">
    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-sm text-white">Carteira</h2>
            @if ($lastChecked)
                <span class="text-gray-600 text-xs">atualizado às {{ $lastChecked }}</span>
            @endif
        </div>
        <button wire:click="check" wire:loading.attr="disabled"
                class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition px-3 py-1.5 rounded-lg hover:bg-gray-800 disabled:opacity-40">
            <svg wire:loading.remove wire:target="check" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg wire:loading wire:target="check" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span wire:loading.remove wire:target="check">Verificar</span>
            <span wire:loading wire:target="check">Verificando...</span>
        </button>
    </div>

    @if (!$hasApiKeys && !$connected)
        {{-- No API keys --}}
        <div class="px-5 py-6 flex flex-col items-center justify-center text-center gap-2">
            <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <p class="text-gray-500 text-sm">Nenhuma chave de API configurada</p>
            <p class="text-gray-600 text-xs">Configure nas <button @click="settingsOpen = true" class="text-green-500 hover:text-green-400 underline">Configurações</button></p>
        </div>

    @elseif ($checking || (!$connected && $lastChecked === null && $hasApiKeys))
        {{-- Loading --}}
        <div class="px-5 py-6 flex items-center justify-center gap-2 text-gray-500 text-sm">
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Conectando à API...
        </div>

    @else
        {{-- Connection status --}}
        <div class="px-5 py-3 flex items-center gap-3 {{ $connected ? 'bg-green-950/20' : 'bg-red-950/20' }} border-b border-gray-800">
            <div class="relative flex items-center justify-center w-3.5 h-3.5 shrink-0">
                @if ($connected)
                    <span class="absolute w-full h-full rounded-full bg-green-400 opacity-30 animate-ping"></span>
                    <span class="relative w-2 h-2 rounded-full bg-green-400"></span>
                @else
                    <span class="relative w-2 h-2 rounded-full bg-red-500"></span>
                @endif
            </div>
            <span class="text-sm font-medium {{ $connected ? 'text-green-400' : 'text-red-400' }}">
                {{ $connectionMessage }}
            </span>
            @if ($connected && $accountType)
                <span class="ml-auto text-xs text-gray-600 bg-gray-800 px-2 py-0.5 rounded-full">{{ $accountType }}</span>
            @endif
        </div>

        @if ($connected && $lowUsdtWarning)
            <div class="px-5 py-2.5 bg-yellow-950/40 border-b border-yellow-900/40 flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="text-yellow-300 text-xs">
                    Saldo USDT insuficiente ({{ number_format($usdtBalance, 2) }} USDT) — mínimo de ordem na Binance é 10 USDT.
                </span>
            </div>
        @endif

        @if ($connected)
            {{-- Account info row --}}
            <div class="px-5 py-2.5 border-b border-gray-800 grid grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-gray-600">Pode operar</span>
                    <div class="{{ $canTrade ? 'text-green-400' : 'text-red-400' }} font-medium mt-0.5">
                        {{ $canTrade ? 'Sim' : 'Não' }}
                    </div>
                </div>
                <div>
                    <span class="text-gray-600">Taxa Maker</span>
                    <div class="text-gray-300 font-medium mt-0.5">{{ $makerFee }}%</div>
                </div>
                <div>
                    <span class="text-gray-600">Taxa Taker</span>
                    <div class="text-gray-300 font-medium mt-0.5">{{ $takerFee }}%</div>
                </div>
            </div>

            {{-- Balances --}}
            @if (!empty($balances))
                <div class="divide-y divide-gray-800/60">
                    @foreach ($balances as $balance)
                    <div class="px-5 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-300">
                                {{ strtoupper(substr($balance['asset'], 0, 2)) }}
                            </span>
                            <span class="text-sm font-medium text-white">{{ $balance['asset'] }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-white font-medium">{{ number_format($balance['free'], 4) }}</div>
                            @if ($balance['locked'] > 0)
                                <div class="text-xs text-yellow-600">{{ number_format($balance['locked'], 4) }} bloqueado</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-6 text-center text-gray-600 text-sm">Carteira vazia</div>
            @endif
        @else
            <div class="px-5 py-4 text-center text-gray-600 text-xs">
                Clique em "Verificar" para testar a conexão
            </div>
        @endif
    @endif
</div>
