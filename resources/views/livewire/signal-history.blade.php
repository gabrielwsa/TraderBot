<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden" wire:poll.10s>
    <div class="px-5 py-3 border-b border-gray-800 flex flex-wrap items-center gap-2 justify-between">
        <h2 class="font-semibold text-sm text-white">Histórico de Sinais</h2>
        <div class="flex gap-1 flex-wrap">
            @foreach (['all' => 'Todos', 'BUY' => 'Compra', 'SELL' => 'Venda', 'HOLD' => 'Hold'] as $val => $label)
            <button wire:click="$set('filter', '{{ $val }}')"
                    class="text-xs px-2.5 py-1 rounded-lg transition {{ $filter === $val ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-white hover:bg-gray-800' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    @if ($signals->isEmpty())
        <div class="px-5 py-8 text-center text-gray-600 text-sm">Nenhum sinal gerado ainda</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-gray-800">
                        <th class="px-5 py-2.5 text-left">Par</th>
                        <th class="px-5 py-2.5 text-left">Sinal</th>
                        <th class="px-5 py-2.5 text-right">Preço</th>
                        <th class="px-5 py-2.5 text-right">EMA 9</th>
                        <th class="px-5 py-2.5 text-right">EMA 21</th>
                        <th class="px-5 py-2.5 text-right">RSI</th>
                        <th class="px-5 py-2.5 text-right">MACD Hist</th>
                        <th class="px-5 py-2.5 text-center">Trade</th>
                        <th class="px-5 py-2.5 text-right">Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @foreach ($signals as $s)
                    <tr class="hover:bg-gray-800/40 transition">
                        <td class="px-5 py-2.5 font-medium text-white">{{ $s->pair }}</td>
                        <td class="px-5 py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $s->signal === 'BUY'  ? 'bg-green-950 text-green-400 border border-green-900' : '' }}
                                {{ $s->signal === 'SELL' ? 'bg-red-950 text-red-400 border border-red-900' : '' }}
                                {{ $s->signal === 'HOLD' ? 'bg-gray-800 text-gray-500 border border-gray-700' : '' }}">
                                {{ $s->signal }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right text-gray-300 font-mono text-xs">{{ number_format($s->price, 4) }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-400 text-xs font-mono">{{ number_format($s->ema9, 4) }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-400 text-xs font-mono">{{ number_format($s->ema21, 4) }}</td>
                        <td class="px-5 py-2.5 text-right text-xs font-mono {{ $s->rsi > 70 ? 'text-red-400' : ($s->rsi < 30 ? 'text-green-400' : 'text-gray-300') }}">
                            {{ number_format($s->rsi, 1) }}
                        </td>
                        <td class="px-5 py-2.5 text-right text-xs font-mono {{ ($s->macd_hist ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ number_format($s->macd_hist, 6) }}
                        </td>
                        <td class="px-5 py-2.5 text-center">
                            @if ($s->traded)
                                <span class="text-green-400 text-xs">✓</span>
                            @else
                                <span class="text-gray-600 text-xs" title="{{ $s->skip_reason }}">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-2.5 text-right text-gray-400 text-xs">{{ $s->created_at->format('d/m H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-800">
            {{ $signals->links() }}
        </div>
    @endif
</div>
