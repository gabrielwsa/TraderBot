<div>
    {{-- No API keys warning --}}
    @if (empty($this->settings->api_key) || empty($this->settings->api_secret))
    <div class="bg-yellow-950/60 border border-yellow-800/60 rounded-xl px-5 py-3 mb-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span class="text-yellow-300 text-sm">Chaves de API não configuradas — o bot não consegue operar.</span>
        </div>
        <button @click="settingsOpen = true"
                class="shrink-0 text-xs bg-yellow-800/50 hover:bg-yellow-700/50 text-yellow-300 px-3 py-1.5 rounded-lg transition">
            Configurar agora
        </button>
    </div>
    @endif

    {{-- Toggle error --}}
    @if ($error)
    <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; $wire.set('error', null) }, 4000)" x-show="show"
         class="bg-red-950/60 border border-red-800/60 rounded-xl px-5 py-3 mb-4 flex items-center gap-3">
        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span class="text-red-300 text-sm">{{ $error }}</span>
    </div>
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
        <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">P&L Total</div>
            <div class="text-2xl font-bold {{ $this->totalPnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $this->totalPnl >= 0 ? '+' : '' }}{{ number_format($this->totalPnl, 4) }}
            </div>
            <div class="text-gray-400 text-xs mt-0.5">USDT</div>
        </div>
        <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Win Rate</div>
            <div class="text-2xl font-bold text-white">{{ $this->winRate }}%</div>
            <div class="text-gray-400 text-xs mt-0.5">{{ $this->totalTrades }} trades</div>
        </div>
        <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Trades Fechados</div>
            <div class="text-2xl font-bold text-white">{{ $this->totalTrades }}</div>
            <div class="text-gray-400 text-xs mt-0.5">histórico</div>
        </div>
        <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Posições Abertas</div>
            <div class="text-2xl font-bold text-white">{{ $this->openPositions }}</div>
            <div class="text-gray-400 text-xs mt-0.5">de {{ $this->settings->max_open_positions }} máx</div>
        </div>
        <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Capital Alocado</div>
            <div class="text-2xl font-bold text-white">{{ number_format($this->settings->capital_usdt, 2) }}</div>
            <div class="text-gray-400 text-xs mt-0.5">USDT</div>
        </div>
    </div>

    {{-- Bot control bar --}}
    <div class="bg-gray-900 border rounded-xl overflow-hidden {{ $this->settings->is_active ? 'border-green-900' : 'border-gray-700' }}">

        {{-- Active indicator strip --}}
        @php $active = $this->settings->is_active; @endphp
        <div class="{{ $active ? 'bg-green-950/40 border-green-900/50' : 'bg-gray-800/40 border-gray-700/50' }} border-b px-5 py-2 flex items-center gap-3">
            <div class="relative flex items-center justify-center w-4 h-4 shrink-0">
                @if ($active)
                    <span class="absolute w-full h-full rounded-full bg-green-400 opacity-30 animate-ping"></span>
                    <span class="relative w-2.5 h-2.5 rounded-full bg-green-400"></span>
                @else
                    <span class="relative w-2.5 h-2.5 rounded-full bg-gray-600"></span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-sm overflow-hidden flex-1">
                @if ($active)
                    @if ($this->openPositions > 0)
                        <span class="text-green-300 font-medium shrink-0">
                            Monitorando {{ $this->openPositions }} {{ $this->openPositions === 1 ? 'posição' : 'posições' }}
                        </span>
                        <span class="text-green-900">•</span>
                    @endif
                    <span class="text-green-700">Escaneando mercados</span>
                    <span class="flex gap-0.5 items-end h-3 shrink-0">
                        <span class="w-0.5 bg-green-600 rounded-full" style="height:40%;animation:bounce 1s ease-in-out infinite"></span>
                        <span class="w-0.5 bg-green-600 rounded-full" style="height:70%;animation:bounce 1s ease-in-out 0.15s infinite"></span>
                        <span class="w-0.5 bg-green-500 rounded-full" style="height:100%;animation:bounce 1s ease-in-out 0.3s infinite"></span>
                        <span class="w-0.5 bg-green-600 rounded-full" style="height:70%;animation:bounce 1s ease-in-out 0.15s infinite"></span>
                        <span class="w-0.5 bg-green-600 rounded-full" style="height:40%;animation:bounce 1s ease-in-out infinite"></span>
                    </span>
                @else
                    <span class="text-gray-600">Bot pausado</span>
                @endif
            </div>
            @if ($active && $this->lastActivity)
            <span class="text-green-900 text-xs shrink-0">último ciclo {{ $this->lastActivity }}</span>
            @endif
        </div>

        {{-- Control row --}}
        <div class="px-5 py-3 flex items-center gap-4">
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <div class="flex items-center gap-2 shrink-0">
                    <span class="w-2 h-2 rounded-full {{ $this->settings->is_active ? 'bg-green-400' : 'bg-gray-600' }}"></span>
                    <span class="font-semibold text-sm {{ $this->settings->is_active ? 'text-green-400' : 'text-gray-400' }}">
                        {{ $this->settings->is_active ? 'Rodando' : 'Parado' }}
                    </span>
                </div>
                <span class="text-gray-700 shrink-0">|</span>
                <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full {{ $this->settings->environment === 'testnet' ? 'bg-yellow-950 text-yellow-400 border border-yellow-900' : 'bg-green-950 text-green-400 border border-green-900' }}">
                        {{ strtoupper($this->settings->environment) }}
                    </span>
                    <span>Timeframe: <span class="text-gray-300">{{ $this->settings->timeframe }}</span></span>
                    <span>Stop Loss: <span class="text-red-400">{{ $this->settings->stop_loss_pct }}%</span></span>
                    <span>Take Profit: <span class="text-green-400">{{ $this->settings->take_profit_pct }}%</span></span>
                    <span>Por trade: <span class="text-gray-300">{{ $this->settings->capital_per_trade_pct }}%</span></span>
                </div>
            </div>

            @php $noApi = empty($this->settings->api_key) || empty($this->settings->api_secret); @endphp
            <button
                wire:click="toggle"
                wire:loading.attr="disabled"
                @if ($noApi && !$this->settings->is_active) disabled title="Configure a API primeiro" @endif
                class="shrink-0 px-5 py-2 rounded-lg font-semibold text-sm transition flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed
                    {{ $this->settings->is_active
                        ? 'bg-red-950 hover:bg-red-900 text-red-400 border border-red-900'
                        : ($noApi ? 'bg-gray-800 text-gray-400 border border-gray-700 cursor-not-allowed' : 'bg-green-600 hover:bg-green-500 text-white') }}"
            >
                <span wire:loading.remove>
                    @if ($this->settings->is_active)
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/>
                        </svg>Parar
                    @elseif ($noApi)
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>Sem API
                    @else
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>Iniciar Bot
                    @endif
                </span>
                <span wire:loading class="opacity-60">aguarde...</span>
            </button>
        </div>
    </div>
</div>
