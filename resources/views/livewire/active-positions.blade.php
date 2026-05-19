<div class="bg-gray-900 rounded-xl border border-gray-800">
    <div class="px-5 py-4 border-b border-gray-800">
        <h2 class="font-semibold text-white">Active Positions <span class="text-gray-500 text-sm">({{ $positions->count() }})</span></h2>
    </div>
    @if ($positions->isEmpty())
        <div class="px-5 py-8 text-center text-gray-500">No open positions</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-gray-800">
                        <th class="px-5 py-3 text-left">Pair</th>
                        <th class="px-5 py-3 text-right">Entry</th>
                        <th class="px-5 py-3 text-right">Current</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Stop Loss</th>
                        <th class="px-5 py-3 text-right">Take Profit</th>
                        <th class="px-5 py-3 text-right">Unrealized P&L</th>
                        <th class="px-5 py-3 text-right">Opened</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($positions as $position)
                    <tr class="hover:bg-gray-800 transition">
                        <td class="px-5 py-3 font-semibold text-white">{{ $position->pair }}</td>
                        <td class="px-5 py-3 text-right text-gray-300">{{ number_format($position->entry_price, 4) }}</td>
                        <td class="px-5 py-3 text-right text-gray-300">{{ number_format($position->current_price, 4) }}</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($position->quantity, 6) }}</td>
                        <td class="px-5 py-3 text-right text-red-400">{{ number_format($position->stop_loss_price, 4) }}</td>
                        <td class="px-5 py-3 text-right text-green-400">{{ number_format($position->take_profit_price, 4) }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $position->unrealized_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $position->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format($position->unrealized_pnl, 4) }}
                            ({{ $position->unrealized_pnl_pct >= 0 ? '+' : '' }}{{ number_format($position->unrealized_pnl_pct, 2) }}%)
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 text-xs">{{ $position->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
