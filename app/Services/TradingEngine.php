<?php

namespace App\Services;

use App\Models\BotLog;
use App\Models\BotSetting;
use App\Models\Position;
use App\Models\Signal;
use App\Models\Trade;
use Exception;

class TradingEngine
{
    private BotSetting $settings;
    private BinanceService $binance;
    private IndicatorService $indicators;

    public function __construct()
    {
        $this->settings = BotSetting::current();
        $this->binance = new BinanceService($this->settings);
        $this->indicators = new IndicatorService();
    }

    public function runCycle(): void
    {
        if (!$this->settings->is_active) return;

        BotLog::info('Starting bot cycle');

        try {
            $this->monitorPositions();
            $this->scanAndTrade();
        } catch (Exception $e) {
            BotLog::error('Bot cycle failed: ' . $e->getMessage());
        }
    }

    public function monitorPositions(): void
    {
        $openPositions = Position::open()->get();

        foreach ($openPositions as $position) {
            try {
                $price = $this->binance->getPrice($position->pair);
                $this->updatePositionPrice($position, $price);

                if ($price <= $position->stop_loss_price) {
                    BotLog::warning("Stop loss triggered for {$position->pair} at {$price}");
                    $this->closePosition($position, 'stop_loss');
                    continue;
                }

                if ($price >= $position->take_profit_price) {
                    BotLog::trade("Take profit triggered for {$position->pair} at {$price}");
                    $this->closePosition($position, 'take_profit');
                    continue;
                }

                $klines = $this->binance->getKlines($position->pair, $this->settings->timeframe, 50);
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

    private function scanAndTrade(): void
    {
        $openCount = Position::open()->count();

        if ($openCount >= $this->settings->max_open_positions) {
            BotLog::info("Max positions reached ({$openCount}/{$this->settings->max_open_positions}), skipping scan");
            return;
        }

        $pairs = $this->binance->getTopUsdtPairs($this->settings->min_volume_usdt, 25);
        $openPairs = Position::open()->pluck('pair')->toArray();
        $pairs = array_diff($pairs, $openPairs);

        BotLog::info('Scanning ' . count($pairs) . ' pairs');

        $slots = $this->settings->max_open_positions - $openCount;

        foreach ($pairs as $pair) {
            if ($slots <= 0) break;

            try {
                $klines = $this->binance->getKlines($pair, $this->settings->timeframe, 100);
                $analysis = $this->indicators->analyze($klines);

                $currentPrice = end($klines)['close'] ?? 0;
                $savedSignal = Signal::create([
                    'pair'        => $pair,
                    'signal'      => $analysis['signal'],
                    'ema9'        => $analysis['ema9'],
                    'ema21'       => $analysis['ema21'],
                    'rsi'         => $analysis['rsi'],
                    'macd_hist'   => $analysis['macd_hist'],
                    'price'       => $currentPrice,
                    'traded'      => false,
                    'skip_reason' => $analysis['signal'] === 'BUY' ? null : 'No signal',
                ]);

                if ($analysis['signal'] === 'BUY') {
                    BotLog::trade("BUY signal for {$pair}: " . implode(', ', $analysis['reasons']));
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
        $feeRate = $this->settings->fee_rate;

        $order = $this->binance->placeBuyOrder($pair, $tradeUsdt);

        $executedQty = (float) ($order['executedQty'] ?? 0);
        $cummulativeQuoteQty = (float) ($order['cummulativeQuoteQty'] ?? $tradeUsdt);
        $avgPrice = $executedQty > 0 ? $cummulativeQuoteQty / $executedQty : $analysis['ema9'];
        $fee = $cummulativeQuoteQty * $feeRate;

        $stopLoss   = $avgPrice * (1 - $this->settings->stop_loss_pct / 100);
        $takeProfit = $avgPrice * (1 + $this->settings->take_profit_pct / 100);

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
            "Opened position: {$pair} @ {$avgPrice} | Qty: {$executedQty} | SL: {$stopLoss} | TP: {$takeProfit}",
            ['position_id' => $position->id]
        );
    }

    private function closePosition(Position $position, string $reason): void
    {
        $feeRate = $this->settings->fee_rate;

        $order = $this->binance->placeSellOrder($position->pair, $position->quantity);

        $executedQty = (float) ($order['executedQty'] ?? $position->quantity);
        $receivedUsdt = (float) ($order['cummulativeQuoteQty'] ?? 0);
        $closePrice = $executedQty > 0 ? $receivedUsdt / $executedQty : $position->current_price;
        $fee = $receivedUsdt * $feeRate;
        $netReceived = $receivedUsdt - $fee;

        $realizedPnl = $netReceived - $position->invested_usdt;
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
