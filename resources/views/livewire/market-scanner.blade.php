<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-sm text-white">Scanner de Mercado</h2>
            @if ($lastUpdated)
                <span class="text-gray-600 text-xs">{{ $lastUpdated === 'cache' ? 'cache' : 'às ' . $lastUpdated }}</span>
            @endif
        </div>
        <button wire:click="refresh" wire:loading.attr="disabled"
                class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition px-3 py-1.5 rounded-lg hover:bg-gray-800 disabled:opacity-40">
            <svg wire:loading.remove wire:target="refresh" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg wire:loading wire:target="refresh" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span wire:loading.remove wire:target="refresh">Atualizar</span>
            <span wire:loading wire:target="refresh">...</span>
        </button>
    </div>

    @if (empty($pairs))
        <div class="px-5 py-8 text-center text-gray-600 text-sm">
            Clique em "Atualizar" para carregar os pares
        </div>
    @else
        <div class="overflow-x-auto max-h-80 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-gray-900 border-b border-gray-800">
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-2.5 text-left">Par</th>
                        <th class="px-5 py-2.5 text-right">Preço</th>
                        <th class="px-5 py-2.5 text-right">24h</th>
                        <th class="px-5 py-2.5 text-right">Volume</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @foreach ($pairs as $pair)
                    <tr class="hover:bg-gray-800/40 transition">
                        <td class="px-5 py-2 font-medium text-white">
                            {{ str_replace('USDT', '', $pair['symbol']) }}
                            <span class="text-gray-600 font-normal">/USDT</span>
                        </td>
                        <td class="px-5 py-2 text-right text-gray-300 font-mono text-xs">
                            {{ number_format($pair['price'], $pair['price'] < 1 ? 6 : ($pair['price'] < 100 ? 4 : 2)) }}
                        </td>
                        <td class="px-5 py-2 text-right font-medium {{ $pair['change_pct'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $pair['change_pct'] >= 0 ? '+' : '' }}{{ $pair['change_pct'] }}%
                        </td>
                        <td class="px-5 py-2 text-right text-gray-500 text-xs">
                            ${{ $pair['volume_usdt'] }}M
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
