<?php

namespace App\Services;

use App\Models\BotLog;
use App\Models\BotSetting;
use App\Models\Position;
use App\Models\Signal;
use App\Models\Trade;
use Exception;
use App\Services\AiAnalysisService;
use App\Services\MarketSentimentService;

class TradingEngine
{
    private BotSetting $settings;
    private BinanceService $binance;
    private IndicatorService $indicators;
    private AiAnalysisService $ai;

    public function __construct()
    {
        $this->settings   = BotSetting::current();
        $this->binance    = new BinanceService($this->settings);
        $this->indicators = new IndicatorService();
        $this->ai         = new AiAnalysisService($this->settings);
    }

    public function runMonitor(): void
    {
        $this->settings->refresh();

        if (!$this->settings->is_active) {
            if (Position::open()->exists()) {
                try {
                    $this->monitorPositions(takeProfitOnly: true);
                } catch (Exception $e) {
                    BotLog::error('Error draining positions: ' . $e->getMessage());
                }
            }
            return;
        }

        try {
            $this->monitorPositions();
        } catch (Exception $e) {
            BotLog::error('Monitor failed: ' . $e->getMessage());
        }
    }

    public function runCycle(): void
    {
        if (!$this->settings->is_active) {
            return;
        }

        BotLog::info('Starting bot cycle');

        try {
            $this->scanAndTrade();
        } catch (Exception $e) {
            BotLog::error('Bot cycle failed: ' . $e->getMessage());
        }
    }

    public function monitorPositions(bool $takeProfitOnly = false): void
    {
        $openPositions = Position::open()->get();

        foreach ($openPositions as $position) {
            try {
                $price = $this->binance->getPrice($position->pair);
                $this->updatePositionPrice($position, $price);

                if ($price >= $position->take_profit_price) {
                    BotLog::trade("Take profit triggered for {$position->pair} at {$price}");
                    $this->closePosition($position, 'take_profit');
                    continue;
                }

                if ($takeProfitOnly) {
                    continue;
                }

                // Trailing stop: raise stop_loss_price as price climbs
                if ($this->settings->trailing_stop_enabled) {
                    $this->updateTrailingStop($position, $price);
                    $position->refresh();
                }

                if ($price <= $position->stop_loss_price) {
                    $reason = $position->stop_loss_price > $position->entry_price * (1 - $this->settings->stop_loss_pct / 100 * 1.01)
                        ? 'trailing_stop'
                        : 'stop_loss';
                    BotLog::warning("Stop loss triggered for {$position->pair} at {$price} [{$reason}]");
                    $this->closePosition($position, $reason);
                    continue;
                }

                $klines  = $this->binance->getKlines($position->pair, $this->settings->timeframe, 50);
                $analysis = $this->indicators->analyze($klines);

                if ($analysis['signal'] === 'SELL') {
                    BotLog::trade("Signal exit for {$position->pair}: " . implode(', ', $analysis['reasons']));
                    $this->closePosition($position, 'signal');
                }
            } catch (Exception $e) {
                BotLog::error("Error monitoring {$position->pair}: " . $e->getMessage());
            }
        }
    }

    private function updateTrailingStop(Position $position, float $price): void
    {
        $trailPct      = $this->settings->trailing_stop_pct / 100;
        $newTrailStop  = $price * (1 - $trailPct);

        if ($newTrailStop > $position->stop_loss_price) {
            $position->update(['stop_loss_price' => $newTrailStop]);
        }
    }

    private function scanAndTrade(): void
    {
        $openCount = Position::open()->count();

        if ($openCount >= $this->settings->max_open_positions) {
            BotLog::info("Max positions reached ({$openCount}/{$this->settings->max_open_positions}), skipping scan");
            return;
        }

        $this->settings->refresh();
        $pairs     = $this->binance->getTopUsdtPairs($this->settings->min_volume_usdt, $this->settings->scan_limit ?? 50, $this->settings->min_volatility_pct);
        $openPairs = Position::open()->pluck('pair')->toArray();
        $blacklist = $this->settings->pair_blacklist ?? [];
        $pairs     = array_values(array_diff($pairs, $openPairs, $blacklist));

        BotLog::info('Scanning ' . count($pairs) . ' pairs');

        // Macro context fetched once, shared across all pairs in this cycle
        try {
            $btcKlines  = $this->binance->getKlines('BTCUSDT', '1h', 50);
            $btcTrend1h = $this->indicators->trendDirection($btcKlines);
        } catch (Exception) {
            $btcTrend1h = 'NEUTRAL';
        }

        $fearGreed = (new MarketSentimentService())->getFearAndGreed();

        $slots = $this->settings->max_open_positions - $openCount;

        foreach ($pairs as $pair) {
            if ($slots <= 0) break;

            try {
                // Multi-timeframe: 1h must not be BEAR (BULL or NEUTRAL ok for daytrade)
                $klines1h = $this->binance->getKlines($pair, '1h', 50);
                $trend1h  = $this->indicators->trendDirection($klines1h);

                if ($trend1h === 'BEAR') {
                    continue;
                }

                $klines   = $this->binance->getKlines($pair, $this->settings->timeframe, 100);
                $analysis = $this->indicators->analyze($klines);

                $currentPrice = end($klines)['close'] ?? 0;
                $savedSignal  = Signal::create([
                    'pair'        => $pair,
                    'signal'      => $analysis['signal'],
                    'ema9'        => $analysis['ema9'],
                    'ema21'       => $analysis['ema21'],
                    'rsi'         => $analysis['rsi'],
                    'macd_hist'   => $analysis['macd_hist'],
                    'price'       => $currentPrice,
                    'traded'      => false,
                    'skip_reason' => $analysis['signal'] === 'BUY' ? null : "No signal (1h trend: {$trend1h})",
                ]);

                if ($analysis['signal'] === 'BUY') {
                    $volLabel = $analysis['volume_ok'] ? 'volume OK' : 'low volume';

                    // Per-pair context for AI (only fetched on actual BUY signal)
                    $orderbook = $this->binance->getOrderbookPressure($pair);
                    $aiContext  = [
                        'btc_trend_1h'     => $btcTrend1h,
                        'fear_greed_value' => $fearGreed['value'],
                        'fear_greed_label' => $fearGreed['label'],
                        'orderbook_ratio'  => $orderbook['ratio'],
                        'funding_rate'     => $this->binance->getFundingRate($pair),
                        'price_change_24h' => round($this->binance->get24hChange($pair), 2),
                    ];

                    // AI validation (if enabled)
                    $aiResult = $this->ai->validateSignal($pair, $analysis, $trend1h, $this->settings->timeframe, $aiContext);

                    if (!$aiResult['proceed']) {
                        BotLog::warning("BUY {$pair} REJEITADO pela IA [{$aiResult['decision']} {$aiResult['confidence']}%]: {$aiResult['reason']}");
                        $savedSignal->update(['skip_reason' => "IA rejeitou: {$aiResult['reason']}"]);
                        continue;
                    }

                    $aiLabel = match($aiResult['decision']) {
                        'CONFIRM'  => "IA confirmou ({$aiResult['confidence']}%)",
                        'NEUTRAL'  => 'IA neutro',
                        'DISABLED' => '',
                        default    => '',
                    };

                    BotLog::trade("BUY signal for {$pair} [1h:{$trend1h}, {$volLabel}" . ($aiLabel ? ", {$aiLabel}" : "") . "]: " . implode(', ', $analysis['reasons']));
                    $this->openPosition($pair, $analysis, $savedSignal);
                    $slots--;
                }
            } catch (Exception $e) {
                BotLog::error("Error analyzing {$pair}: " . $e->getMessage());
            }
        }
    }

    private function openPosition(string $pair, array $analysis, ?Signal $savedSignal = null): void
    {
        $tradeUsdt = $this->settings->capital_usdt * ($this->settings->capital_per_trade_pct / 100);
        $feeRate   = $this->settings->fee_rate;

        $order = $this->binance->placeBuyOrder($pair, $tradeUsdt);

        $executedQty         = (float) ($order['executedQty'] ?? 0);
        $cummulativeQuoteQty = (float) ($order['cummulativeQuoteQty'] ?? $tradeUsdt);
        $avgPrice            = $executedQty > 0 ? $cummulativeQuoteQty / $executedQty : $analysis['ema9'];
        $fee                 = $cummulativeQuoteQty * $feeRate;

        // ATR-based SL/TP: adapt to each coin's real volatility
        $atr = $analysis['atr'] ?? 0;

        if ($atr > 0) {
            // SL: tighter of ATR*1.5 or configured %, protecting from over-wide stops
            $atrSlDist = $atr * 1.5;
            $cfgSlDist = $avgPrice * ($this->settings->stop_loss_pct / 100);
            $slDist    = min($atrSlDist, $cfgSlDist);

            // TP: wider of ATR*3.0 or configured %, letting winners run
            $atrTpDist = $atr * 3.0;
            $cfgTpDist = $avgPrice * ($this->settings->take_profit_pct / 100);
            $tpDist    = max($atrTpDist, $cfgTpDist);
        } else {
            $slDist = $avgPrice * ($this->settings->stop_loss_pct / 100);
            $tpDist = $avgPrice * ($this->settings->take_profit_pct / 100);
        }

        $stopLoss   = $avgPrice - $slDist;
        $takeProfit = $avgPrice + $tpDist;

        $slPct = round(($slDist / $avgPrice) * 100, 2);
        $tpPct = round(($tpDist / $avgPrice) * 100, 2);

        $position = Position::create([
            'pair'              => $pair,
            'entry_price'       => $avgPrice,
            'quantity'          => $executedQty,
            'invested_usdt'     => $cummulativeQuoteQty,
            'current_price'     => $avgPrice,
            'stop_loss_price'   => $stopLoss,
            'take_profit_price' => $takeProfit,
            'total_fees_usdt'   => $fee,
            'buy_order_id'      => $order['orderId'] ?? null,
            'status'            => 'open',
        ]);

        Trade::create([
            'position_id'  => $position->id,
            'pair'         => $pair,
            'side'         => 'BUY',
            'quantity'     => $executedQty,
            'price'        => $avgPrice,
            'total_usdt'   => $cummulativeQuoteQty,
            'fee_usdt'     => $fee,
            'fee_asset'    => 'USDT',
            'order_id'     => $order['orderId'] ?? null,
            'order_status' => $order['status'] ?? 'FILLED',
        ]);

        if ($savedSignal) {
            $savedSignal->update(['traded' => true]);
        }

        BotLog::trade(
            "Opened position: {$pair} @ {$avgPrice} | Qty: {$executedQty} | SL: {$stopLoss} (-{$slPct}%) | TP: {$takeProfit} (+{$tpPct}%) | ATR: " . round($atr, 6),
            ['position_id' => $position->id]
        );
    }

    private function closePosition(Position $position, string $reason): void
    {
        $feeRate = $this->settings->fee_rate;

        $order = $this->binance->placeSellOrder($position->pair, $position->quantity);

        $executedQty   = (float) ($order['executedQty'] ?? $position->quantity);
        $receivedUsdt  = (float) ($order['cummulativeQuoteQty'] ?? 0);
        $closePrice    = $executedQty > 0 ? $receivedUsdt / $executedQty : $position->current_price;
        $fee           = $receivedUsdt * $feeRate;
        $netReceived   = $receivedUsdt - $fee;

        $realizedPnl    = $netReceived - $position->invested_usdt;
        $realizedPnlPct = ($realizedPnl / $position->invested_usdt) * 100;

        $position->update([
            'status'           => 'closed',
            'close_price'      => $closePrice,
            'realized_pnl'     => $realizedPnl,
            'realized_pnl_pct' => $realizedPnlPct,
            'total_fees_usdt'  => $position->total_fees_usdt + $fee,
            'close_reason'     => $reason,
            'sell_order_id'    => $order['orderId'] ?? null,
        ]);

        Trade::create([
            'position_id'  => $position->id,
            'pair'         => $position->pair,
            'side'         => 'SELL',
            'quantity'     => $executedQty,
            'price'        => $closePrice,
            'total_usdt'   => $receivedUsdt,
            'fee_usdt'     => $fee,
            'fee_asset'    => 'USDT',
            'order_id'     => $order['orderId'] ?? null,
            'order_status' => $order['status'] ?? 'FILLED',
        ]);

        $pnlSign = $realizedPnl >= 0 ? '+' : '';
        BotLog::trade(
            "Closed {$position->pair} [{$reason}] @ {$closePrice} | PnL: {$pnlSign}" . round($realizedPnl, 4) . " USDT (" . round($realizedPnlPct, 2) . "%)",
            ['position_id' => $position->id]
        );
    }

    private function updatePositionPrice(Position $position, float $price): void
    {
        $unrealizedPnl = ($price - $position->entry_price) * $position->quantity;
        $unrealizedPct = (($price / $position->entry_price) - 1) * 100;

        $position->update([
            'current_price'      => $price,
            'unrealized_pnl'     => $unrealizedPnl,
            'unrealized_pnl_pct' => $unrealizedPct,
        ]);
    }
}
