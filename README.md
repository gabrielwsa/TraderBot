# BotTrade — Bot de Trading Automatizado para Binance Spot

---

## Iniciando do Zero

### Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) instalado
- [Docker Compose](https://docs.docker.com/compose/install/) instalado
- Nada mais — PHP, Node, Composer não precisam estar instalados na máquina

---

### Passo a Passo

**1. Clone o repositório**

```bash
git clone <url-do-repositorio>
cd botTrande
```

**2. Crie o arquivo de ambiente**

```bash
cp .env.example .env
```

**3. Suba os containers**

```bash
docker compose up -d --build
```

Isso vai construir as imagens e subir 6 serviços: `app`, `nginx`, `db`, `redis`, `scheduler` e `queue`. O agendador (`schedule:work`) e o processador de filas (`queue:work`) já sobem automaticamente — não é necessário nenhum comando extra para o bot funcionar.

**4. Instale as dependências PHP**

```bash
docker compose exec app composer install
```

**5. Gere a chave da aplicação**

```bash
docker compose exec app php artisan key:generate
```

**6. Rode as migrations**

```bash
docker compose exec app php artisan migrate
```

**7. Crie o primeiro usuário**

Acesse [http://localhost:8000/register](http://localhost:8000/register) e crie sua conta.

**8. Configure a API da Binance**

Após fazer login, clique no seu nome no canto superior direito → **Configurações** e insira sua API Key e API Secret. Para testar sem risco real, selecione o ambiente **Testnet** e use chaves geradas em [testnet.binance.vision](https://testnet.binance.vision).

---

### Comandos úteis

| Comando | O que faz |
|---|---|
| `docker compose up -d` | Sobe todos os serviços em segundo plano |
| `docker compose down` | Para e remove os containers |
| `docker compose down -v` | Para, remove containers e apaga o volume do banco |
| `docker compose logs -f queue` | Acompanha os logs do worker de filas |
| `docker compose logs -f scheduler` | Acompanha os logs do agendador |
| `docker compose exec app php artisan migrate` | Roda migrations pendentes |
| `docker compose exec app php artisan tinker` | REPL interativo do Laravel |
| `docker compose ps` | Lista o status de todos os serviços |

---

## O que o Projeto Faz

BotTrade é um bot de trading automatizado que opera no mercado Spot da Binance. Ele analisa os pares de criptomoedas mais negociados em USDT, identifica oportunidades de compra usando uma combinação de indicadores técnicos e executa ordens de forma autônoma, gerenciando Stop Loss e Take Profit de cada posição aberta.

### Estratégia: Triple Signal

O bot só abre uma posição quando os três indicadores concordam simultaneamente:

1. **EMA 9/21 Crossover** — a média móvel rápida (9 períodos) cruza acima da lenta (21 períodos), indicando início de tendência de alta
2. **RSI 14 entre 35 e 65** — o ativo não está sobrecomprado nem sobrevendido, há margem de movimento
3. **MACD Histogram positivo e crescendo** — o momentum confirma a direção da tendência

Quando uma posição está aberta, o bot monitora continuamente:
- **Stop Loss** — vende automaticamente se o preço cair o percentual configurado
- **Take Profit** — vende automaticamente ao atingir o lucro alvo
- **Saída por sinal** — fecha a posição se os indicadores reversarem (EMA cruzar para baixo)

### Dashboard

O dashboard exibe em tempo real:
- Status do bot e controle de ligar/desligar
- Estatísticas do dia (P&L, trades, win rate, taxas)
- Gráfico de P&L acumulado ao longo do tempo
- Posições abertas com P&L não realizado
- Informações da carteira e conexão com a API
- Log de atividade do bot
- Scanner de mercado com os 20 pares mais negociados
- Rastreador de taxas pagas à Binance (dia/semana/mês/total)
- Histórico de sinais gerados com todos os valores dos indicadores
- Histórico completo de trades fechados

---

## Dados Técnicos

### Stack

| Tecnologia | Versão | Por que foi escolhida |
|---|---|---|
| **Laravel** | 11 | Framework PHP com filas, agendador e ORM nativos — tudo que o bot precisa sem dependências extras |
| **Livewire** | 3 | Componentes reativos server-side; o dashboard atualiza sozinho via polling sem escrever JavaScript |
| **Alpine.js** | 3 (bundled no Livewire) | Interatividade de UI leve (dropdowns, modais, tooltips) sem precisar de build step |
| **Tailwind CSS** | via CDN | Estilização utilitária diretamente no HTML; sem Node nem npm nos containers |
| **Chart.js** | 4.4.0 via CDN | Gráfico de P&L com zero dependências de build |
| **MySQL** | 8.0 | Banco relacional para positions, trades, signals e configurações |
| **Redis** | 7 | Driver de filas e cache; garante que cada ciclo do bot processe exatamente uma vez |
| **PHP** | 8.3-FPM | Versão atual do PHP com suporte a fibers e atributos modernos |
| **Nginx** | alpine | Proxy reverso que serve os assets públicos e passa as requisições PHP para o FPM |
| **predis/predis** | latest | Cliente Redis em PHP puro — dispensa a extensão phpredis no container |

---

### Arquitetura dos Containers

```
nginx:8000 ──► app:9000 (PHP-FPM)
                  │
                  ├── db (MySQL 8)
                  └── redis (Redis 7)

scheduler ──► php artisan schedule:work
                  └── dispara RunBotCycle a cada minuto

queue ──► php artisan queue:work redis
              └── processa os jobs RunBotCycle
```

Todos os serviços PHP (`app`, `scheduler`, `queue`) usam a mesma imagem, construída com `UID/GID=1000` para que os arquivos criados dentro do container pertençam ao usuário do host.

---

### Estrutura de Arquivos Relevante

```
app/
├── Http/Controllers/
│   ├── AuthController.php       # Login, registro e logout
│   └── DashboardController.php  # Rota principal do dashboard
├── Jobs/
│   └── RunBotCycle.php          # Job que roda a cada minuto via queue
├── Livewire/
│   ├── BotStatus.php            # Controle ligar/desligar + stats gerais
│   ├── TodayStats.php           # Estatísticas do dia atual
│   ├── PnlChart.php             # Dados do gráfico de P&L
│   ├── ActivePositions.php      # Posições abertas com P&L em tempo real
│   ├── WalletInfo.php           # Conexão com API + saldos da carteira
│   ├── ActivityLog.php          # Log de atividade do bot
│   ├── MarketScanner.php        # Top 20 pares USDT por volume
│   ├── FeeTracker.php           # Taxas pagas agrupadas por período
│   ├── SignalHistory.php        # Histórico paginado de sinais gerados
│   ├── TradeHistory.php         # Histórico paginado de trades fechados
│   └── BotSettings.php          # Formulário de configurações
├── Models/
│   ├── BotSetting.php           # Singleton de configuração (firstOrCreate)
│   ├── Position.php             # Trade aberto ou fechado
│   ├── Trade.php                # Registro de cada ordem executada
│   ├── Signal.php               # Sinal gerado por par analisado
│   └── ActivityLog.php          # Entrada do log de atividade
└── Services/
    ├── BinanceService.php        # Toda comunicação com a API da Binance (REST + HMAC-SHA256)
    ├── IndicatorService.php      # Cálculo de EMA, RSI e MACD
    └── TradingEngine.php         # Lógica central: escanear, decidir, abrir e fechar posições

docker/
├── php/Dockerfile               # Imagem PHP 8.3-FPM com extensões necessárias
└── nginx/default.conf           # Configuração do servidor web

routes/
├── web.php                      # Rotas HTTP (login, register, dashboard, logout)
└── console.php                  # Agendamento: RunBotCycle a cada minuto
```

---

### Fluxo de um Ciclo do Bot

```
schedule:work (1 min)
    └── dispara RunBotCycle no Redis

queue:work
    └── RunBotCycle::handle()
          └── TradingEngine::runCycle()
                ├── monitorPositions()
                │     └── para cada Position aberta:
                │           ├── busca preço atual na Binance
                │           ├── verifica Stop Loss → fecha se atingido
                │           ├── verifica Take Profit → fecha se atingido
                │           └── verifica sinal de saída (EMA cross reverso)
                └── scanAndTrade()
                      └── busca top pares por volume (BinanceService)
                            └── para cada par:
                                  ├── busca klines (candles)
                                  ├── IndicatorService::analyze()
                                  │     ├── calcula EMA 9 e EMA 21
                                  │     ├── calcula RSI 14
                                  │     └── calcula MACD (12/26/9)
                                  ├── salva Signal no banco
                                  └── se BUY e posições disponíveis:
                                        └── openPosition() → ordem market buy na Binance
```

---

### Variáveis de Ambiente Importantes

| Variável | Valor padrão | Descrição |
|---|---|---|
| `DB_HOST` | `db` | Nome do serviço MySQL no Docker |
| `REDIS_HOST` | `redis` | Nome do serviço Redis no Docker |
| `REDIS_CLIENT` | `predis` | Usa o cliente PHP puro (sem extensão C) |
| `QUEUE_CONNECTION` | `redis` | Filas processadas pelo Redis |
| `CACHE_STORE` | `redis` | Cache do scanner e dados temporários |
| `SESSION_DRIVER` | `redis` | Sessões de autenticação no Redis |

As chaves da Binance (`api_key`, `api_secret`) são armazenadas no banco de dados via painel de configurações, não no `.env`.
