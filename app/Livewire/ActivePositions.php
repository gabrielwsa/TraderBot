<?php

namespace App\Livewire;

use App\Models\BotLog;
use App\Models\BotSetting;
use App\Models\Position;
use App\Models\Trade;
use App\Services\BinanceService;
use Exception;
use Livewire\Component;

class ActivePositions extends Component
{
    public ?string $sellError = null;

    public function manualSell(int $positionId): void
    {
        $this->sellError = null;
        $position = Position::open()->find($positionId);

        if (!$position) {
            $this->sellError = 'Posição não encontrada ou já fechada.';
            return;
        }

        $settings = BotSetting::current();

        try {
            $binance      = new BinanceService($settings);
            $order        = $binance->placeSellOrder($position->pair, $position->quantity);
            $executedQty  = (float) ($order['executedQty'] ?? $position->quantity);
            $receivedUsdt = (float) ($order['cummulativeQuoteQty'] ?? 0);
            $closePrice   = $executedQty > 0 ? $receivedUsdt / $executedQty : $position->current_price;
            $fee          = $receivedUsdt * $settings->fee_rate;
            $netReceived  = $receivedUsdt - $fee;
            $realizedPnl  = $netReceived - $position->invested_usdt;
            $realizedPct  = ($realizedPnl / $position->invested_usdt) * 100;

            $position->update([
                'status'           => 'closed',
                'close_price'      => $closePrice,
                'close_reason'     => 'manual',
                'realized_pnl'     => $realizedPnl,
                'realized_pnl_pct' => $realizedPct,
                'total_fees_usdt'  => $position->total_fees_usdt + $fee,
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

            $sign = $realizedPnl >= 0 ? '+' : '';
            BotLog::trade("Venda manual: {$position->pair} @ {$closePrice} | PnL: {$sign}" . round($realizedPnl, 4) . " USDT (" . round($realizedPct, 2) . "%)");
        } catch (Exception $e) {
            $this->sellError = 'Erro ao vender: ' . $e->getMessage();
            BotLog::error("Erro na venda manual de {$position->pair}: " . $e->getMessage());
        }
    }

    public function render()
    {
        $settings  = BotSetting::current();
        $positions = Position::open()->orderByDesc('created_at')->get()->map(function ($p) use ($settings) {
            $range    = $p->take_profit_price - $p->stop_loss_price;
            $progress = $range > 0
                ? max(0, min(100, (($p->current_price - $p->stop_loss_price) / $range) * 100))
                : 50;

            $distToSl       = $p->current_price > 0 ? (($p->current_price - $p->stop_loss_price) / $p->current_price) * 100 : 0;
            $distToTp       = $p->current_price > 0 ? (($p->take_profit_price - $p->current_price) / $p->current_price) * 100 : 0;
            $initialSl      = $p->entry_price * (1 - $settings->stop_loss_pct / 100);
            $trailingActive = $settings->trailing_stop_enabled && $p->stop_loss_price > $initialSl * 1.001;
            $profitLocked   = $p->stop_loss_price >= $p->entry_price;

            return (object) [
                'id'                => $p->id,
                'pair'              => $p->pair,
                'entry_price'       => $p->entry_price,
                'current_price'     => $p->current_price,
                'quantity'          => $p->quantity,
                'invested_usdt'     => $p->invested_usdt,
                'stop_loss_price'   => $p->stop_loss_price,
                'take_profit_price' => $p->take_profit_price,
                'unrealized_pnl'    => $p->unrealized_pnl,
                'unrealized_pnl_pct'=> $p->unrealized_pnl_pct,
                'created_at'        => $p->created_at,
                'progress'          => round($progress, 1),
                'dist_to_sl'        => round($distToSl, 2),
                'dist_to_tp'        => round($distToTp, 2),
                'trailing_active'   => $trailingActive,
                'profit_locked'     => $profitLocked,
            ];
        });

        return view('livewire.active-positions', compact('positions'));
    }
}
