<div class="grid grid-cols-4 gap-3">
    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
        <div class="flex items-center justify-between mb-1">
            <div class="text-gray-500 text-xs uppercase tracking-wider">P&L Hoje</div>
            <span class="text-xs text-gray-600">{{ now()->format('d/m') }}</span>
        </div>
        <div class="text-2xl font-bold {{ $this->todayPnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
            {{ $this->todayPnl >= 0 ? '+' : '' }}{{ number_format($this->todayPnl, 4) }}
        </div>
        <div class="text-gray-600 text-xs mt-0.5">USDT</div>
    </div>
    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
        <div class="text-gray-500 text-xs uppercase tracking-wider mb-1">Trades Hoje</div>
        <div class="text-2xl font-bold text-white">{{ $this->todayTrades }}</div>
        <div class="text-gray-600 text-xs mt-0.5">operações</div>
    </div>
    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
        <div class="text-gray-500 text-xs uppercase tracking-wider mb-1">Win Rate Hoje</div>
        <div class="text-2xl font-bold text-white">{{ $this->todayWinRate }}%</div>
        <div class="text-gray-600 text-xs mt-0.5">{{ $this->todayTrades }} trades</div>
    </div>
    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
        <div class="text-gray-500 text-xs uppercase tracking-wider mb-1">Taxas Hoje</div>
        <div class="text-2xl font-bold text-red-400">-{{ number_format($this->todayFees, 4) }}</div>
        <div class="text-gray-600 text-xs mt-0.5">USDT pagas</div>
    </div>
</div>
