# Troubleshooting

Problemas conhecidos e como resolver.

---

## Class "Redis" not found

**Sintoma**
```
queue-1  | App\Jobs\RunBotCycle ... FAIL
queue-1  | Class "Redis" not found
queue-1  | at vendor/laravel/framework/.../PhpRedisConnector.php
```

**Causa**
O Laravel está tentando usar o cliente `phpredis` (extensão C), que não está instalada no container. O projeto usa `predis` (PHP puro).

**Solução**
Verifique se o `.env` está correto:
```bash
grep REDIS_CLIENT .env
# deve retornar: REDIS_CLIENT=predis
```

Se estiver correto mas o erro persistir, o cache de configuração está desatualizado:
```bash
docker compose exec app php artisan config:cache
docker compose restart queue scheduler
```

---

## Vite manifest not found

**Sintoma**
```
Vite manifest not found at: /var/www/public/build/manifest.json
```

**Causa**
O Laravel está tentando carregar assets compilados pelo Vite/npm, que não existe no ambiente Docker deste projeto.

**Solução**
Substituir `@vite()` no layout por Tailwind CSS via CDN. O arquivo correto já usa CDN — se aparecer esse erro, verifique se `resources/views/components/layouts/app.blade.php` contém:
```html
<script src="https://cdn.tailwindcss.com"></script>
```
e não `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

---

## Unable to locate component [layouts.app]

**Sintoma**
```
Unable to locate a class or view for component [layouts.app]
```

**Causa**
O componente `<x-layouts.app>` resolve para `resources/views/components/layouts/app.blade.php`, não para `resources/views/layouts/app.blade.php`.

**Solução**
Certifique-se que o arquivo está em:
```
resources/views/components/layouts/app.blade.php
```

---

## Jobs no queue completando em ~1ms sem fazer nada

**Sintoma**
```
queue-1  | App\Jobs\RunBotCycle ... 1.10ms DONE
```
Os jobs completam muito rápido e nada aparece no Activity Log.

**Causa**
O bot está desativado (`is_active = false`) e não há posições abertas — o `runCycle()` retorna imediatamente. Comportamento esperado.

**Solução**
Ligue o bot pelo dashboard clicando em **Iniciar Bot**. Após ligar, os jobs devem demorar mais (~100ms+) e o Activity Log deve mostrar `Starting bot cycle`.

---

## Bot ligado mas não abre nenhuma posição

**Causa mais comum**
Os 3 indicadores (EMA 9/21 + RSI + MACD) não estão alinhando — o mercado está lateral ou sem tendência clara. Comportamento esperado e correto.

**O que verificar**
1. Abra o **Signal History** no dashboard — se aparecem registros a cada minuto com sinal `HOLD`, o bot está funcionando e só não encontrou oportunidade
2. Se o Signal History estiver vazio, rode:
```bash
docker compose logs -f queue
```
e veja se os jobs estão completando sem erro

**Ajuste possível**
Baixar o **Volume Mínimo 24h** nas configurações (ex: de 1.000.000 para 100.000) aumenta o número de pares escaneados e as chances de sinal.

---

## Saldo aparece como 0 na validação do capital

**Sintoma**
Ao salvar configurações aparece: `Capital maior que o saldo disponível (0 USDT)`

**Causa**
A carteira tem `USD` em vez de `USDT`. São ativos diferentes na Binance — o bot opera pares USDT e as ordens serão rejeitadas com saldo apenas em USD.

**Solução**
Converter USD para USDT dentro da Binance (Carteira → Converter) antes de operar.

---

## Carteira fica travada em "Conectando à API..."

**Causa**
A API da Binance não respondeu dentro do timeout de 10 segundos — pode ser problema de rede, IP não autorizado ou API Key inválida.

**O que verificar**
1. Confirme que seu IP público está na whitelist da API Key na Binance (Gestão de API → editar chave → IP access restrictions)
2. Para descobrir seu IP público: `curl ifconfig.me`
3. Se usar IP dinâmico (residencial), o IP pode ter mudado — atualize na Binance

---

## Ordens rejeitadas pela Binance (MIN_NOTIONAL)

**Sintoma**
Activity Log mostra erro de ordem, bot não abre posição mesmo com sinal BUY.

**Causa**
O valor por operação está abaixo do mínimo de 10 USDT exigido pela Binance no Spot.

**Solução**
Nas configurações, garantir que:
```
Capital (USDT) × Capital por Trade (%) ÷ 100 ≥ 10
```
Exemplo: 10 USDT × 100% = 10 USDT ✓

---

## Bot compra pares da blacklist mesmo após salvar configurações

**Sintoma**
O bot abre posição em um par que está na blacklist, logo após você ter salvo as configurações.

**Causa**
O queue worker é um processo de longa duração. Se um ciclo do bot já estava em execução quando você salvou, ele usou as configurações antigas até o fim daquele ciclo. Além disso, qualquer alteração de código só entra em vigor após reiniciar o worker.

**Solução**
Sempre que salvar configurações importantes (blacklist, volatilidade), reinicie o worker:
```bash
docker compose restart queue
```

Se uma posição indevida já foi aberta, feche manualmente pelo tinker:
```bash
docker compose exec app php artisan tinker
```
```php
use App\Models\Position;
$p = Position::where('pair', 'BTCUSDT')->where('status', 'open')->first();
$p->update(['status' => 'closed', 'close_reason' => 'manual', 'close_price' => $p->current_price]);
```

---

## Arquivos criados no container pertencem ao root

**Sintoma**
Arquivos gerados dentro do container (logs, cache) pertencem ao usuário `root` e não podem ser editados pelo usuário do host.

**Causa**
A imagem foi buildada sem os argumentos `UID`/`GID`.

**Solução**
Rebuildar a imagem passando os argumentos corretos:
```bash
docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
docker compose up -d
```
