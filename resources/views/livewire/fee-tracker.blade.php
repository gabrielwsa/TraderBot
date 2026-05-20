<div class="bg-gray-900 border border-gray-700 rounded-xl p-5 h-full">
    <h2 class="font-semibold text-sm text-white mb-4">Taxas Pagas à Binance</h2>

    <div class="space-y-3">
        <div class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400 text-sm">Hoje</span>
            <span class="text-red-400 font-medium">-{{ number_format($this->feesToday, 4) }} USDT</span>
        </div>
        <div class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400 text-sm">Esta semana</span>
            <span class="text-red-400 font-medium">-{{ number_format($this->feesThisWeek, 4) }} USDT</span>
        </div>
        <div class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400 text-sm">Este mês</span>
            <span class="text-red-400 font-medium">-{{ number_format($this->feesThisMonth, 4) }} USDT</span>
        </div>
        <div class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400 text-sm">Total acumulado</span>
            <span class="text-red-400 font-bold">-{{ number_format($this->totalFees, 4) }} USDT</span>
        </div>

        <div class="bg-gray-800/50 rounded-lg p-3 mt-2">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-xs">Impacto das taxas no resultado</span>
                <span class="text-gray-300 text-xs font-medium">{{ $this->feeImpactPct }}%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-1.5">
                <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ min($this->feeImpactPct, 100) }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-400">
                <span>P&L bruto: {{ $this->grossPnl >= 0 ? '+' : '' }}{{ number_format($this->grossPnl, 4) }}</span>
                <span>Líquido: {{ ($this->grossPnl - $this->totalFees) >= 0 ? '+' : '' }}{{ number_format($this->grossPnl - $this->totalFees, 4) }}</span>
            </div>
        </div>
    </div>
</div>
