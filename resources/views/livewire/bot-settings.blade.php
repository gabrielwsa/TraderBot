@php
$tips = [
    'api_key'               => 'Chave pública gerada no painel da Binance. Identifica sua conta. Acesse: Binance → Perfil → Gestão de API.',
    'api_secret'            => 'Chave privada exibida apenas uma vez ao criar a API. Guarde com cuidado — ela autoriza as operações.',
    'environment'           => 'Testnet usa saldo fictício para testar sem risco. Produção usa dinheiro real na sua conta Binance.',
    'capital_usdt'          => 'Total em USDT que o bot pode usar para abrir trades. O saldo restante da sua conta não é tocado.',
    'capital_per_trade_pct' => 'Percentual do capital total usado por operação. Ex: 10% de 100 USDT = 10 USDT por trade. Menos % = menos risco por operação.',
    'max_open_positions'    => 'Quantidade máxima de trades abertos ao mesmo tempo. O bot não abre novos trades se esse limite for atingido.',
    'stop_loss_pct'         => 'Percentual de queda máxima tolerada antes de vender automaticamente. Ex: 2% = vende se o preço cair 2% do ponto de entrada.',
    'take_profit_pct'       => 'Percentual de lucro alvo para fechar o trade automaticamente. Ex: 4% = vende ao atingir 4% de ganho.',
    'timeframe'             => 'Intervalo dos candles usados para calcular os indicadores (EMA, RSI, MACD). 15m é o mais usado para day trade.',
    'min_volume_usdt'       => 'Volume mínimo negociado em 24h para um par ser considerado. Filtra moedas sem liquidez — quanto maior, mais seguro.',
    'use_bnb_fees'          => 'Se você tiver BNB na carteira, a Binance desconta as taxas em BNB com 25% de desconto (0.075% em vez de 0.1%).',
];
@endphp

{{-- Macro: label + tooltip wrapper. Each has its own x-data so hover on the tooltip itself doesn't close it. --}}
@php
function tipBtn(string $key, string $label, array $tips): string { return ''; }
@endphp

<div>

    @if ($saved)
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
         class="bg-green-950 border border-green-800 text-green-300 px-4 py-3 rounded-lg text-sm mb-5 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Configurações salvas com sucesso.
    </div>
    @endif

    {{-- API Credentials --}}
    <div class="mb-6">
        <h3 class="text-gray-500 text-xs uppercase tracking-wider mb-4">Credenciais da API</h3>
        <div class="space-y-4">

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">API Key</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['api_key'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="api_key" type="text" placeholder="Sua API Key da Binance"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none font-mono" />
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">API Secret</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['api_secret'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="api_secret" type="password" placeholder="Sua API Secret"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none font-mono" />
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Ambiente</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['environment'] }}
                        </div>
                    </div>
                </div>
                <select wire:model="environment" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none">
                    <option value="testnet">Testnet — saldo fictício, sem risco</option>
                    <option value="production">Produção — dinheiro real</option>
                </select>
            </div>

        </div>
    </div>

    {{-- Capital & Risk --}}
    <div class="border-t border-gray-800 pt-6 mb-6">
        <h3 class="text-gray-500 text-xs uppercase tracking-wider mb-4">Capital & Risco</h3>
        <div class="grid grid-cols-2 gap-4">

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Capital (USDT)</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['capital_usdt'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="capital_usdt" type="number" step="0.01"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none" />
                @error('capital_usdt') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Capital por Trade (%)</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['capital_per_trade_pct'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="capital_per_trade_pct" type="number" step="0.1" min="1" max="100"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none" />
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Posições Simultâneas</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['max_open_positions'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="max_open_positions" type="number" min="1" max="20"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none" />
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Timeframe</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['timeframe'] }}
                        </div>
                    </div>
                </div>
                <select wire:model="timeframe" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none">
                    <option value="1m">1 minuto</option>
                    <option value="5m">5 minutos</option>
                    <option value="15m">15 minutos (recomendado)</option>
                    <option value="1h">1 hora</option>
                </select>
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Stop Loss (%)</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['stop_loss_pct'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="stop_loss_pct" type="number" step="0.1" min="0.1"
                       class="w-full bg-gray-800 border border-red-900/50 rounded-lg px-4 py-2.5 text-red-300 text-sm focus:border-red-500 focus:outline-none" />
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Take Profit (%)</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['take_profit_pct'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="take_profit_pct" type="number" step="0.1" min="0.1"
                       class="w-full bg-gray-800 border border-green-900/50 rounded-lg px-4 py-2.5 text-green-300 text-sm focus:border-green-500 focus:outline-none" />
            </div>

            <div class="col-span-2">
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="text-sm text-gray-400">Volume Mínimo 24h (USDT)</label>
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                        <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                            {{ $tips['min_volume_usdt'] }}
                        </div>
                    </div>
                </div>
                <input wire:model="min_volume_usdt" type="number" step="100000"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none" />
            </div>

            <div class="col-span-2 flex items-start gap-3 bg-gray-800/50 rounded-lg p-3">
                <input wire:model="use_bnb_fees" type="checkbox" id="bnb_fees" class="w-4 h-4 mt-0.5 accent-green-500 shrink-0" />
                <div class="flex-1">
                    <div class="flex items-center gap-1.5">
                        <label for="bnb_fees" class="text-sm text-gray-300 cursor-pointer">Pagar taxas com BNB</label>
                        <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                            <button class="w-4 h-4 rounded-full bg-gray-700 hover:bg-gray-600 text-gray-400 text-xs flex items-center justify-center leading-none transition">?</button>
                            <div x-show="show" x-cloak class="absolute left-5 top-0 z-50 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 text-xs text-gray-300 shadow-xl">
                                {{ $tips['use_bnb_fees'] }}
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 text-xs mt-0.5">0.075% por trade em vez de 0.1% — economiza 25% em taxas</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Risk summary --}}
    <div class="border border-gray-800 rounded-lg p-4 bg-gray-800/30 text-xs text-gray-500 mb-6 space-y-1">
        <div class="text-gray-400 font-medium mb-2">Resumo de risco por trade</div>
        <div class="flex justify-between">
            <span>Valor por operação</span>
            <span class="text-white">{{ number_format($capital_usdt * ($capital_per_trade_pct / 100), 2) }} USDT</span>
        </div>
        <div class="flex justify-between">
            <span>Perda máxima por trade</span>
            <span class="text-red-400">-{{ number_format($capital_usdt * ($capital_per_trade_pct / 100) * ($stop_loss_pct / 100), 2) }} USDT</span>
        </div>
        <div class="flex justify-between">
            <span>Ganho alvo por trade</span>
            <span class="text-green-400">+{{ number_format($capital_usdt * ($capital_per_trade_pct / 100) * ($take_profit_pct / 100), 2) }} USDT</span>
        </div>
        <div class="flex justify-between border-t border-gray-700 pt-1 mt-1">
            <span>Risco/Retorno</span>
            <span class="text-gray-300">1 : {{ $stop_loss_pct > 0 ? number_format($take_profit_pct / $stop_loss_pct, 1) : '—' }}</span>
        </div>
    </div>

    <button wire:click="save" wire:loading.attr="disabled"
            class="w-full bg-green-600 hover:bg-green-500 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition disabled:opacity-50 flex items-center justify-center gap-2">
        <span wire:loading.remove>Salvar Configurações</span>
        <span wire:loading class="flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Salvando...
        </span>
    </button>

</div>
