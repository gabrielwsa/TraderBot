<div x-data="{ open: false, tab: 'lateral' }" class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">

    {{-- Header --}}
    <button @click="open = !open"
        class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-gray-800 transition-colors">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="text-sm font-semibold text-gray-200">Guia de Estratégias</span>
            <span class="text-xs text-gray-500">— o que funciona em cada condição de mercado</span>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Body --}}
    <div x-show="open" x-transition class="border-t border-gray-700">

        {{-- Tabs --}}
        <div class="flex border-b border-gray-700 bg-gray-900">
            <button @click="tab = 'lateral'"
                :class="tab === 'lateral' ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-400 hover:text-gray-200'"
                class="px-5 py-2.5 text-xs font-medium transition-colors">
                Mercado Lateral
            </button>
            <button @click="tab = 'alta'"
                :class="tab === 'alta' ? 'border-b-2 border-green-400 text-green-400' : 'text-gray-400 hover:text-gray-200'"
                class="px-5 py-2.5 text-xs font-medium transition-colors">
                Mercado em Alta
            </button>
            <button @click="tab = 'queda'"
                :class="tab === 'queda' ? 'border-b-2 border-red-400 text-red-400' : 'text-gray-400 hover:text-gray-200'"
                class="px-5 py-2.5 text-xs font-medium transition-colors">
                Mercado em Queda
            </button>
            <button @click="tab = 'taxas'"
                :class="tab === 'taxas' ? 'border-b-2 border-blue-400 text-blue-400' : 'text-gray-400 hover:text-gray-200'"
                class="px-5 py-2.5 text-xs font-medium transition-colors">
                Taxas &amp; Math
            </button>
            <button @click="tab = 'sl'"
                :class="tab === 'sl' ? 'border-b-2 border-purple-400 text-purple-400' : 'text-gray-400 hover:text-gray-200'"
                class="px-5 py-2.5 text-xs font-medium transition-colors">
                Stop Loss
            </button>
        </div>

        {{-- Tab: Lateral --}}
        <div x-show="tab === 'lateral'" class="p-5 space-y-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 shrink-0">LATERAL</span>
                <p class="text-sm text-gray-300">O preço oscila pra cima e pra baixo sem tendência clara. BTC trend = NEUTRAL, Fear & Greed entre 30–60. É o mercado mais comum e onde bots de scalping brilham.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-yellow-400 uppercase tracking-wide">Estratégia ideal: Scalping</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-yellow-400">•</span> Timeframe: <span class="text-white font-medium">5m</span></li>
                        <li class="flex gap-2"><span class="text-yellow-400">•</span> Take Profit: <span class="text-white font-medium">0.4–0.6%</span></li>
                        <li class="flex gap-2"><span class="text-yellow-400">•</span> Stop Loss: <span class="text-white font-medium">0.35–0.4%</span></li>
                        <li class="flex gap-2"><span class="text-yellow-400">•</span> Trailing Stop: <span class="text-white font-medium">desligado</span></li>
                        <li class="flex gap-2"><span class="text-yellow-400">•</span> Volatilidade mínima: <span class="text-white font-medium">0.5%</span></li>
                    </ul>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-yellow-400 uppercase tracking-wide">Por que funciona?</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Muitas moedas sobem/caem 0.4–0.6% várias vezes por dia</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> TP pequeno é atingido antes de reverter</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Volume de trades compensa o lucro pequeno por trade</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Mais trades = mais taxa total paga</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Mais falsos sinais no 5m</li>
                    </ul>
                </div>
            </div>

            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3 text-xs text-yellow-300">
                <strong>Situação atual do bot:</strong> BTC está em BEAR 1h com RSI ~39. Mercado lateral/queda. Aguardar o BTC virar NEUTRAL é o caminho mais seguro antes de scalpar.
            </div>
        </div>

        {{-- Tab: Alta --}}
        <div x-show="tab === 'alta'" class="p-5 space-y-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-green-500/20 text-green-400 shrink-0">ALTA</span>
                <p class="text-sm text-gray-300">BTC em tendência de subida, Fear & Greed acima de 60. As moedas sobem mais rápido e os sinais BUY aparecem com frequência. É o melhor momento para o bot.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-green-400 uppercase tracking-wide">Estratégia ideal: Swing</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-green-400">•</span> Timeframe: <span class="text-white font-medium">15m ou 1h</span></li>
                        <li class="flex gap-2"><span class="text-green-400">•</span> Take Profit: <span class="text-white font-medium">1.5–3%</span></li>
                        <li class="flex gap-2"><span class="text-green-400">•</span> Stop Loss: <span class="text-white font-medium">1–1.5%</span></li>
                        <li class="flex gap-2"><span class="text-green-400">•</span> Trailing Stop: <span class="text-white font-medium">ligado (0.5–0.8%)</span></li>
                        <li class="flex gap-2"><span class="text-green-400">•</span> Volatilidade mínima: <span class="text-white font-medium">1%</span></li>
                    </ul>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-green-400 uppercase tracking-wide">Por que funciona?</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Moedas podem subir 2–5% numa hora em bull market</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Trailing stop deixa o lucro crescer enquanto sobe</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Menos trades = menos taxa total</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Se o mercado virar, posições abertas tomam prejuízo maior</li>
                    </ul>
                </div>
            </div>

            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-3 text-xs text-green-300">
                <strong>Dica:</strong> Em bull market, Fear & Greed acima de 70 (Greed) é sinal de atenção — o mercado pode estar perto de uma correção. Considere reduzir o número máximo de posições abertas.
            </div>
        </div>

        {{-- Tab: Queda --}}
        <div x-show="tab === 'queda'" class="p-5 space-y-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 shrink-0">QUEDA</span>
                <p class="text-sm text-gray-300">BTC em tendência de queda, Fear & Greed abaixo de 25 (Extreme Fear). A maioria das moedas cai junto com BTC. O bot já filtra o 1h BEAR — menos sinais é o comportamento certo.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wide">O que fazer</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Deixar o bot pausado ou em modo conservador</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Reduzir capital por trade para 5–10%</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Manter max posições em 2–3</li>
                        <li class="flex gap-2"><span class="text-green-400">✓</span> Stop Loss bem apertado (0.5–0.8%)</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Nunca desativar o stop loss esperando recuperar</li>
                    </ul>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wide">O que NÃO fazer</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Abrir muitas posições esperando reversão</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Aumentar o TP achando que vai recuperar mais</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Desligar o stop loss — a moeda pode cair 20–50%</li>
                        <li class="flex gap-2"><span class="text-red-400">✗</span> Comprar a queda sem confirmação de reversão</li>
                    </ul>
                </div>
            </div>

            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-xs text-red-300">
                <strong>Sobre o bot do seu tio:</strong> 97% de acerto com 18 posições abertas no vermelho = ele nunca fecha losers. Parece incrível mas se o mercado cair mais 10%, todas as 18 posições viram prejuízo realizado de uma vez. Ter stop loss real protege o capital.
            </div>
        </div>

        {{-- Tab: Taxas --}}
        <div x-show="tab === 'taxas'" class="p-5 space-y-4">
            <p class="text-sm text-gray-300">A Binance cobra <span class="text-white font-semibold">0.1% por ordem</span> (maker e taker). Cada trade completo = compra + venda = <span class="text-white font-semibold">~0.2% de custo</span>.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2 text-gray-400 font-medium">Take Profit</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Taxa (~0.2%)</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Lucro líquido</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Em $200 de posição</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Viável?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr>
                            <td class="py-2 text-gray-300">0.2%</td>
                            <td class="py-2 text-gray-400">-0.20%</td>
                            <td class="py-2 text-red-400 font-medium">≈ 0%</td>
                            <td class="py-2 text-red-400">$0.00</td>
                            <td class="py-2"><span class="text-red-400">Não</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-300">0.35%</td>
                            <td class="py-2 text-gray-400">-0.20%</td>
                            <td class="py-2 text-yellow-400 font-medium">+0.15%</td>
                            <td class="py-2 text-yellow-400">$0.30</td>
                            <td class="py-2"><span class="text-yellow-400">Mínimo</span></td>
                        </tr>
                        <tr class="bg-green-500/5">
                            <td class="py-2 text-gray-300">0.5%</td>
                            <td class="py-2 text-gray-400">-0.20%</td>
                            <td class="py-2 text-green-400 font-medium">+0.30%</td>
                            <td class="py-2 text-green-400">$0.60</td>
                            <td class="py-2"><span class="text-green-400">Scalping</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-300">1.0%</td>
                            <td class="py-2 text-gray-400">-0.20%</td>
                            <td class="py-2 text-green-400 font-medium">+0.80%</td>
                            <td class="py-2 text-green-400">$1.60</td>
                            <td class="py-2"><span class="text-green-400">Bom</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-300">2.0%</td>
                            <td class="py-2 text-gray-400">-0.20%</td>
                            <td class="py-2 text-green-400 font-medium">+1.80%</td>
                            <td class="py-2 text-green-400">$3.60</td>
                            <td class="py-2"><span class="text-green-400">Bull market</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3 text-xs text-blue-300">
                <strong>Dica BNB:</strong> Se você pagar as taxas com BNB na Binance, o desconto é 25% — a taxa cai de 0.1% para 0.075% por ordem, economizando ~0.05% por trade completo.
            </div>
        </div>

        {{-- Tab: Stop Loss --}}
        <div x-show="tab === 'sl'" class="p-5 space-y-4">
            <p class="text-sm text-gray-300">Stop loss é a proteção mais importante do bot. Parece que você perde dinheiro quando ele dispara, mas na verdade ele está <span class="text-white font-semibold">protegendo o capital maior</span>.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-green-400 uppercase tracking-wide">Com Stop Loss (nosso bot)</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-gray-500">Ex:</span> Entra em $100 com SL 1%</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Preço cai 1% → fecha com -$1</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Capital restante: $99</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Pode entrar novamente e recuperar</li>
                        <li class="flex gap-2 mt-2"><span class="text-green-400">✓</span> <strong class="text-white">Risco controlado e previsível</strong></li>
                    </ul>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wide">Sem Stop Loss (estratégia do tio)</p>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li class="flex gap-2"><span class="text-gray-500">Ex:</span> Entra em $100 sem SL</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Preço cai 20% → segura esperando</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Capital preso: -$20 ainda no papel</li>
                        <li class="flex gap-2"><span class="text-gray-500">→</span> Se cair mais 50% → -$70 realizado</li>
                        <li class="flex gap-2 mt-2"><span class="text-red-400">✗</span> <strong class="text-white">Risco ilimitado e invisível</strong></li>
                    </ul>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-4 space-y-2">
                <p class="text-xs font-semibold text-purple-400 uppercase tracking-wide">A matemática do recovery</p>
                <div class="grid grid-cols-4 gap-2 text-xs">
                    <div class="text-center p-2 bg-gray-700 rounded">
                        <div class="text-red-400 font-bold">-10%</div>
                        <div class="text-gray-400">precisa +11% pra recuperar</div>
                    </div>
                    <div class="text-center p-2 bg-gray-700 rounded">
                        <div class="text-red-400 font-bold">-25%</div>
                        <div class="text-gray-400">precisa +33% pra recuperar</div>
                    </div>
                    <div class="text-center p-2 bg-gray-700 rounded">
                        <div class="text-red-400 font-bold">-50%</div>
                        <div class="text-gray-400">precisa +100% pra recuperar</div>
                    </div>
                    <div class="text-center p-2 bg-gray-700 rounded">
                        <div class="text-red-400 font-bold">-80%</div>
                        <div class="text-gray-400">precisa +400% pra recuperar</div>
                    </div>
                </div>
            </div>

            <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-3 text-xs text-purple-300">
                <strong>Conclusão:</strong> Perder 1% 10 vezes = -10%. Perder 50% uma vez = precisa dobrar o capital pra voltar ao zero. Stop loss pequeno e frequente é matematicamente melhor do que segurar um grande prejuízo.
            </div>
        </div>

    </div>
</div>
