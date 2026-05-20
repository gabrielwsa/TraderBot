<div class="bg-gray-900 rounded-xl border border-gray-700 h-full" wire:poll.4s
     x-data="{ confirm: false, posId: null, posPair: null, posPnl: null, posPnlPositive: true }"
     @keydown.escape.window="confirm = false">

    <div class="px-5 py-4 border-b border-gray-700">
        <h2 class="font-semibold text-white">Active Positions <span class="text-gray-400 text-sm">({{ $positions->count() }})</span></h2>
    </div>

    {{-- Sell error banner --}}
    @if ($sellError)
    <div class="mx-5 mt-3 bg-red-950 border border-red-800 text-red-300 px-4 py-2.5 rounded-lg text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $sellError }}
    </div>
    @endif

    @if ($positions->isEmpty())
        <div class="px-5 py-8 text-center text-gray-400">No open positions</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700">
                        <th class="px-5 py-3 text-left">Pair</th>
                        <th class="px-5 py-3 text-right">Entry</th>
                        <th class="px-5 py-3 text-right">Current</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Stop Loss</th>
                        <th class="px-5 py-3 text-right">Take Profit</th>
                        <th class="px-5 py-3 text-right">Unrealized P&L</th>
                        <th class="px-5 py-3 text-right">Opened</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($positions as $p)
                    @php
                        if ($p->progress >= 85)       { $badge = ['ALVO',  'text-green-300 bg-green-900/50 border-green-800/50', '']; $rowClass = 'bg-green-950/30 hover:bg-green-950/50 border-l-2 border-l-green-500'; }
                        elseif ($p->profit_locked)     { $badge = ['GANHO', 'text-green-300 bg-green-900/50 border-green-800/50', '']; $rowClass = 'bg-green-950/20 hover:bg-green-950/40 border-l-2 border-l-green-700'; }
                        elseif ($p->trailing_active)   { $badge = ['TSL',   'text-blue-300 bg-blue-900/50 border-blue-800/50',   '']; $rowClass = 'bg-blue-950/20 hover:bg-blue-950/40 border-l-2 border-l-blue-600'; }
                        elseif ($p->dist_to_sl < 0.5)  { $badge = ['RISCO', 'text-red-300 bg-red-900/50 border-red-800/50',     'animate-pulse']; $rowClass = 'bg-red-950/30 hover:bg-red-950/50 border-l-2 border-l-red-500 animate-pulse'; }
                        else                           { $badge = null; $rowClass = 'hover:bg-gray-800'; }
                    @endphp
                    <tr class="transition {{ $rowClass }}">
                        <td class="px-5 py-3 font-semibold text-white">
                            <div class="flex items-center gap-2">
                                {{ $p->pair }}
                                @if ($badge)
                                <span class="px-1.5 py-0.5 rounded text-xs border {{ $badge[1] }} {{ $badge[2] }}">
                                    {{ $badge[0] }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-300">{{ number_format($p->entry_price, 4) }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $p->unrealized_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ number_format($p->current_price, 4) }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($p->quantity, 6) }}</td>
                        <td class="px-5 py-3 text-right text-red-400">{{ number_format($p->stop_loss_price, 4) }}</td>
                        <td class="px-5 py-3 text-right text-green-400">{{ number_format($p->take_profit_price, 4) }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $p->unrealized_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $p->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format($p->unrealized_pnl, 4) }}
                            ({{ $p->unrealized_pnl_pct >= 0 ? '+' : '' }}{{ number_format($p->unrealized_pnl_pct, 2) }}%)
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400 text-xs">{{ $p->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="https://www.binance.com/en/trade/{{ str_replace('USDT', '_USDT', $p->pair) }}"
                                   target="_blank" rel="noopener"
                                   title="Ver gráfico na Binance"
                                   class="p-1.5 rounded-lg bg-gray-800 hover:bg-yellow-950 text-gray-400 hover:text-yellow-400 border border-gray-700 hover:border-yellow-900 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                    </svg>
                                </a>
                                <button
                                    @click="confirm = true; posId = {{ $p->id }}; posPair = '{{ $p->pair }}'; posPnl = '{{ ($p->unrealized_pnl >= 0 ? '+' : '') . number_format($p->unrealized_pnl, 4) }} USDT ({{ ($p->unrealized_pnl_pct >= 0 ? '+' : '') . number_format($p->unrealized_pnl_pct, 2) }}%)'; posPnlPositive = {{ $p->unrealized_pnl >= 0 ? 'true' : 'false' }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-800 hover:bg-red-950 text-gray-400 hover:text-red-300 border border-gray-700 hover:border-red-900 transition">
                                    Vender
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Confirmation modal --}}
    <div x-show="confirm" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        {{-- backdrop --}}
        <div class="absolute inset-0 bg-black/70" @click="confirm = false"></div>

        <div class="relative bg-gray-900 border border-gray-700 rounded-xl shadow-2xl w-full max-w-sm p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-start gap-3 mb-5">
                <div class="w-9 h-9 rounded-full bg-red-950 border border-red-900 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white text-base">Confirmar venda</h3>
                    <p class="text-gray-400 text-sm mt-0.5">
                        Vender <span class="text-white font-semibold" x-text="posPair"></span> agora ao preço de mercado?
                    </p>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg px-4 py-3 mb-5 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">P&L atual</span>
                    <span class="font-semibold" :class="posPnlPositive ? 'text-green-400' : 'text-red-400'" x-text="posPnl"></span>
                </div>
                <p class="text-gray-600 text-xs mt-1">A ordem será executada ao preço de mercado — o valor pode variar ligeiramente.</p>
            </div>

            <div class="flex gap-3">
                <button @click="confirm = false"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 transition">
                    Cancelar
                </button>
                <button
                    @click="confirm = false"
                    wire:click="manualSell(posId)"
                    wire:loading.attr="disabled"
                    class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold bg-red-900 hover:bg-red-800 text-red-200 border border-red-800 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="manualSell">Confirmar Venda</span>
                    <span wire:loading wire:target="manualSell">Vendendo...</span>
                </button>
            </div>
        </div>
    </div>
</div>
