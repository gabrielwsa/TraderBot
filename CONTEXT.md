# CONTEXT — botTrande

Estado atual do projeto para referência em novas sessões de desenvolvimento.

---

## Stack

- **Backend:** Laravel 11, PHP, SQLite (via Docker)
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS (via CDN)
- **Infra:** Docker Compose — containers `app`, `queue`, `scheduler`
- **Exchange:** Binance Spot (testnet e produção)
- **Idioma da UI:** Português (Brasil)

---

## Arquitetura do trading loop

```
scheduler (every minute)
    → RunBotCycle job → queue worker
        → TradingEngine::runCycle()
            → monitorPositions()   ← atualiza preços, trailing stop, TP/SL/signal exit
            → scanAndTrade()       ← escaneia pares, abre posições novas
```

**Serviços principais:**
- `BinanceService` — toda comunicação com a API da Binance (timeout 10s em todas as chamadas)
- `IndicatorService` — cálculo de EMA, RSI, MACD, ATR, volume spike, trendDirection
- `TradingEngine` — orquestra o ciclo completo

---

## Algoritmo de trading atual

### Filtros de entrada (scanAndTrade)
1. Volume mínimo 24h (configurável, padrão 1M USDT)
2. Volatilidade mínima 24h abs (configurável, padrão 1%) — elimina pares estáveis como EUR/USDT
3. Par não está na blacklist configurada pelo usuário
4. Par não tem posição aberta
5. **Multi-timeframe:** tendência do 1h deve ser BULL (EMA9 > EMA21 e preço acima de ambos)
6. **Sinal no timeframe configurado (padrão 15m):** EMA 9/21 bullish crossover + RSI 35–65 + MACD histogram positivo e crescente
7. **Volume spike:** volume do candle atual ≥ 1.5× média dos últimos 20 (informativo, não bloqueante — sinal fraco é logado mas ainda aceito)

### SL/TP — baseados em ATR
- **SL** = menor entre `ATR × 1.5` e `stop_loss_pct` configurado (mais preciso, protege mais)
- **TP** = maior entre `ATR × 3.0` e `take_profit_pct` configurado (deixa lucro correr)
- Se ATR = 0 (klines insuficientes), usa percentuais fixos configurados

### Saída de posições (monitorPositions)
1. Take profit atingido → fecha em lucro
2. Trailing stop atualizado a cada ciclo: `stop = max_price × (1 - trailing_pct)` — só sobe, nunca desce
3. Stop loss atingido → fecha (identifica se foi trailing ou stop fixo no log)
4. Sinal SELL (EMA bearish crossover) → fecha por sinal

### Comportamento ao parar o bot
Posições abertas continuam sendo monitoradas **somente para take profit** (sem SL, sem signal exit) até todas fecharem — modo "drain".

---

## Componentes Livewire (todos no dashboard)

| Componente | Poll | Descrição |
|---|---|---|
| `BotStatus` | 3s (wrapper) | Controle start/stop, stats gerais, badge ambiente, parâmetros ativos |
| `TodayStats` | 10s (wrapper) | P&L do dia, trades, win rate, taxas |
| `PnlChart` | 30s (wrapper) | Gráfico P&L acumulado (dispara evento JS) |
| `ActivePositions` | 4s (root div) | Tabela de posições abertas |
| `WalletInfo` | manual | Carteira Binance — só USDT/USD, aviso < 10 USDT |
| `ActivityLog` | 3s (root div) | Últimos 50 logs, scroll fixo h-64 |
| `MarketScanner` | 60s (root div) | Top 20 pares por volume, destacado dimmed se volatilidade abaixo do mínimo |
| `FeeTracker` | — | Taxas acumuladas (hoje, semana, mês, total) |
| `SignalHistory` | 10s (root div) | Histórico paginado de sinais, filtro BUY/SELL/HOLD |
| `TradeHistory` | 10s (wrapper) | Histórico paginado de posições fechadas |
| `BotSettings` | — | Modal de configurações com validação live |

**Regra importante:** `wire:poll` deve estar no root div do próprio componente, NÃO em um wrapper no dashboard — senão o poll não funciona.

---

## Banco de dados

### `bot_settings` (1 registro)
| Coluna | Padrão | Descrição |
|---|---|---|
| `environment` | testnet | testnet / production |
| `capital_usdt` | 100 | Capital total disponível para o bot |
| `capital_per_trade_pct` | 10 | % do capital por operação |
| `max_open_positions` | 3 | Máximo de posições simultâneas |
| `stop_loss_pct` | 2.0 | Stop loss máximo em % |
| `take_profit_pct` | 4.0 | Take profit mínimo em % |
| `trailing_stop_enabled` | true | Trailing stop ativo |
| `trailing_stop_pct` | 1.0 | Distância do trailing stop em % |
| `use_bnb_fees` | false | Usar BNB para pagar taxas (0.075% vs 0.1%) |
| `min_volume_usdt` | 1000000 | Volume mínimo 24h do par |
| `min_volatility_pct` | 1.0 | Variação mínima 24h abs do par |
| `timeframe` | 15m | Timeframe dos candles (1m/5m/15m/1h) |
| `pair_blacklist` | null | JSON array de pares bloqueados |
| `is_active` | false | Bot ligado ou desligado |

### `positions`
`pair`, `entry_price`, `quantity`, `invested_usdt`, `current_price`, `unrealized_pnl/pct`, `stop_loss_price` (atualizado pelo trailing), `take_profit_price`, `status` (open/closed), `close_price`, `realized_pnl/pct`, `total_fees_usdt`, `close_reason` (stop_loss / trailing_stop / take_profit / signal / manual), `buy_order_id`, `sell_order_id`

### `trades`
`position_id` (FK), `pair`, `side` (BUY/SELL), `quantity`, `price`, `total_usdt`, `fee_usdt`, `order_id`, `order_status`

### `bot_logs`
`level` (info/warning/error/trade), `message`, `context` (JSON)

### `signals`
`pair`, `signal` (BUY/SELL/HOLD), `ema9`, `ema21`, `rsi`, `macd_hist`, `price`, `traded`, `skip_reason`

---

## Configurações da UI (BotSettings)

Todos os campos numéricos críticos usam `wire:model.live` para validação em tempo real.

**Alertas inline implementados:**
- Capital por trade < 10 USDT → alerta vermelho (mínimo Binance)
- Exposição total > 80% do capital → alerta amarelo
- Volatilidade mínima = 0% → aviso de pares estáveis
- Volatilidade mínima ≥ 5% → aviso de poucos pares
- Capital > saldo real na Binance → erro de validação (via API)

**Trailing stop:** toggle on/off + campo de % com `wire:model.live`

**Blacklist:** campo de busca com dropdown Alpine.js mostrando preço, variação 24h e volume — reusa cache `scanner_pairs` (sem chamadas extras à API)

---

## Infraestrutura Docker

```bash
docker compose up -d          # sobe todos os containers
docker compose restart queue  # SEMPRE fazer após mudar código PHP
docker compose exec app php artisan migrate
docker compose exec app php artisan config:cache
docker compose logs -f queue  # ver jobs rodando
```

**Containers:**
- `app` — servidor web Laravel
- `queue` — `php artisan queue:work` — executa RunBotCycle jobs
- `scheduler` — `php artisan schedule:work` — dispara jobs a cada minuto

---

## Comandos úteis (tinker)

**Fechar posição manualmente:**
```php
use App\Models\Position;
$p = Position::where('pair', 'XYZUSDT')->where('status', 'open')->first();
$p->update(['status' => 'closed', 'close_reason' => 'manual', 'close_price' => $p->current_price]);
```

**Ver blacklist atual:**
```php
use App\Models\BotSetting;
BotSetting::current()->pair_blacklist;
```

**Ver últimos logs:**
```php
use App\Models\BotLog;
BotLog::orderByDesc('id')->limit(10)->get(['level','message','created_at']);
```

---

## Problemas conhecidos e soluções

Ver `TROUBLESHOOTING.md` para lista completa. Resumo dos mais comuns:

| Problema | Solução rápida |
|---|---|
| Bot compra par da blacklist | `docker compose restart queue` após salvar settings |
| Carteira trava em "Conectando..." | Timeout de 10s — IP pode não estar na whitelist da API Key |
| Job FAIL: Class "Redis" not found | `config:cache` + restart queue/scheduler |
| Capital validado como 0 USDT | Conta tem USD não USDT — converter na Binance |
| Bot ativo mas sem trades | Mercado lateral — verificar Signal History para ver sinais HOLD |

---

## O que JÁ está implementado (não refazer)

- ✅ Dashboard responsivo mobile (grid sm:grid-cols-2)
- ✅ Contraste de cores melhorado em todos os componentes
- ✅ Timeout 10s em todas as chamadas Binance
- ✅ WalletInfo: auto-connect no mount, aviso < 10 USDT, só USDT/USD
- ✅ Activity Log: scroll fixo, poll 3s
- ✅ Signal History: poll 10s, paginação compacta (onEachSide 1)
- ✅ Market Scanner: auto-refresh 60s, pares bloqueados dimmed
- ✅ Bot status bar: faixa sempre visível (verde=rodando, cinza=parado)
- ✅ Drain on stop: fecha posições abertas somente no TP ao parar o bot
- ✅ Blacklist: busca com dropdown Alpine, tags removíveis, persiste em JSON
- ✅ Volatilidade mínima: filtro por variação 24h no scanner e no bot
- ✅ Settings refresh() antes de escanear (fix race condition)
- ✅ ATR-based SL/TP dinâmico
- ✅ Trailing stop (toggle on/off, % configurável)
- ✅ Multi-timeframe: confirma tendência no 1h antes de entrar no 15m
- ✅ Volume spike: detecta confirmação de volume, loga sinal fraco
- ✅ README.md e TROUBLESHOOTING.md na raiz do projeto
