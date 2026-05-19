<div class="bg-gray-900 rounded-xl border border-gray-800">
    <div class="px-5 py-4 border-b border-gray-800">
        <h2 class="font-semibold text-white">Trade History</h2>
    </div>
    @if ($positions->isEmpty())
        <div class="px-5 py-8 text-center text-gray-500">No trades yet</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-gray-800">
                        <th class="px-5 py-3 text-left">Pair</th>
                        <th class="px-5 py-3 text-right">Entry</th>
                        <th class="px-5 py-3 text-right">Exit</th>
                        <th class="px-5 py-3 text-right">P&L</th>
                        <th class="px-5 py-3 text-right">Fees</th>
                        <th class="px-5 py-3 text-right">Reason</th>
                        <th class="px-5 py-3 text-right">Closed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($positions as $position)
                    <tr class="hover:bg-gray-800 transition">
                        <td class="px-5 py-3 font-semibold text-white">{{ $position->pair }}</td>
                        <td class="px-5 py-3 text-right text-gray-300">{{ number_format($position->entry_price, 4) }}</td>
                        <td class="px-5 py-3 text-right text-gray-300">{{ number_format($position->close_price, 4) }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $position->realized_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $position->realized_pnl >= 0 ? '+' : '' }}{{ number_format($position->realized_pnl, 4) }} USDT
                            <span class="text-xs">({{ number_format($position->realized_pnl_pct, 2) }}%)</span>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ number_format($position->total_fees_usdt, 4) }}</td>
                        <td class="px-5 py-3 text-right">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $position->close_reason === 'take_profit' ? 'bg-green-900 text-green-300' : '' }}
                                {{ $position->close_reason === 'stop_loss' ? 'bg-red-900 text-red-300' : '' }}
                                {{ $position->close_reason === 'signal' ? 'bg-blue-900 text-blue-300' : '' }}
                                {{ $position->close_reason === 'manual' ? 'bg-gray-700 text-gray-300' : '' }}
                            ">{{ $position->close_reason }}</span>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 text-xs">{{ $position->updated_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-800">
                {{ $positions->links() }}
            </div>
        </div>
    @endif
</div>
