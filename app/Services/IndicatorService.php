<?php

namespace App\Services;

class IndicatorService
{
    public function ema(array $closes, int $period): array
    {
        if (count($closes) < $period) return [];

        $k = 2 / ($period + 1);
        $ema = [];
        $ema[] = array_sum(array_slice($closes, 0, $period)) / $period;

        for ($i = $period; $i < count($closes); $i++) {
            $ema[] = $closes[$i] * $k + end($ema) * (1 - $k);
        }

        return $ema;
    }

    public function rsi(array $closes, int $period = 14): float
    {
        if (count($closes) < $period + 1) return 50.0;

        $changes = [];
        for ($i = 1; $i < count($closes); $i++) {
            $changes[] = $closes[$i] - $closes[$i - 1];
        }

        $gains = array_map(fn($c) => max($c, 0), $changes);
        $losses = array_map(fn($c) => abs(min($c, 0)), $changes);

        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        for ($i = $period; $i < count($changes); $i++) {
            $avgGain = ($avgGain * ($period - 1) + $gains[$i]) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $losses[$i]) / $period;
        }

        if ($avgLoss == 0) return 100.0;
        $rs = $avgGain / $avgLoss;
        return 100 - (100 / (1 + $rs));
    }

    public function macd(array $closes, int $fast = 12, int $slow = 26, int $signal = 9): array
    {
        $emaFast = $this->ema($closes, $fast);
        $emaSlow = $this->ema($closes, $slow);

        $offset = $slow - $fast;
        $macdLine = [];
        for ($i = 0; $i < count($emaSlow); $i++) {
            $macdLine[] = $emaFast[$i + $offset] - $emaSlow[$i];
        }

        $signalLine = $this->ema($macdLine, $signal);
        $offsetSig = count($macdLine) - count($signalLine);

        $histogram = [];
        for ($i = 0; $i < count($signalLine); $i++) {
            $histogram[] = $macdLine[$i + $offsetSig] - $signalLine[$i];
        }

        return [
            'macd'      => $macdLine,
            'signal'    => $signalLine,
            'histogram' => $histogram,
        ];
    }

    // Average True Range — measures volatility per candle
    public function atr(array $klines, int $period = 14): float
    {
        if (count($klines) < $period + 1) return 0.0;

        $trueRanges = [];
        for ($i = 1; $i < count($klines); $i++) {
            $high      = $klines[$i]['high'];
            $low       = $klines[$i]['low'];
            $prevClose = $klines[$i - 1]['close'];
            $trueRanges[] = max($high - $low, abs($high - $prevClose), abs($low - $prevClose));
        }

        // Wilder smoothing
        $atr = array_sum(array_slice($trueRanges, 0, $period)) / $period;
        for ($i = $period; $i < count($trueRanges); $i++) {
            $atr = ($atr * ($period - 1) + $trueRanges[$i]) / $period;
        }

        return $atr;
    }

    // Returns true if the last candle has a volume spike vs. recent average
    public function volumeSpike(array $klines, int $period = 20, float $multiplier = 1.5): bool
    {
        if (count($klines) < $period + 1) return false;

        $volumes     = array_column($klines, 'volume');
        $lastVolume  = end($volumes);
        $avgVolume   = array_sum(array_slice($volumes, -($period + 1), $period)) / $period;

        return $avgVolume > 0 && $lastVolume >= $avgVolume * $multiplier;
    }

    // Higher-timeframe trend: BULL if EMA9 > EMA21, BEAR if below, NEUTRAL otherwise
    public function trendDirection(array $klines): string
    {
        $closes = array_column($klines, 'close');
        $ema9   = $this->ema($closes, 9);
        $ema21  = $this->ema($closes, 21);

        if (empty($ema9) || empty($ema21)) return 'NEUTRAL';

        $lastEma9  = end($ema9);
        $lastEma21 = end($ema21);
        $lastClose = end($closes);

        if ($lastEma9 > $lastEma21 && $lastClose > $lastEma9) return 'BULL';
        if ($lastEma9 < $lastEma21 && $lastClose < $lastEma9) return 'BEAR';
        return 'NEUTRAL';
    }

    public function analyze(array $klines): array
    {
        $closes  = array_column($klines, 'close');

        $ema9  = $this->ema($closes, 9);
        $ema21 = $this->ema($closes, 21);
        $rsi   = $this->rsi($closes, 14);
        $macd  = $this->macd($closes);
        $atr   = $this->atr($klines, 14);
        $volOk = $this->volumeSpike($klines, 20, 1.5);

        $lastEma9  = end($ema9);
        $prevEma9  = $ema9[count($ema9) - 2] ?? $lastEma9;
        $lastEma21 = end($ema21);
        $prevEma21 = $ema21[count($ema21) - 2] ?? $lastEma21;

        $lastHistogram = end($macd['histogram']) ?: 0;
        $prevHistogram = $macd['histogram'][count($macd['histogram']) - 2] ?? 0;

        $bullishCrossover = $prevEma9 <= $prevEma21 && $lastEma9 > $lastEma21;
        $bearishCrossover = $prevEma9 >= $prevEma21 && $lastEma9 < $lastEma21;
        $rsiOk    = $rsi >= 35 && $rsi <= 65;
        $macdBull = $lastHistogram > 0 && $lastHistogram > $prevHistogram;

        $signal  = 'HOLD';
        $reasons = [];

        if ($bullishCrossover && $rsiOk && $macdBull && $volOk) {
            $signal  = 'BUY';
            $reasons = [
                'EMA 9/21 bullish crossover',
                'RSI ' . round($rsi, 1) . ' in range',
                'MACD histogram rising',
                'Volume spike confirmed',
            ];
        } elseif ($bullishCrossover && $rsiOk && $macdBull && !$volOk) {
            // Signal without volume — still valid but flagged
            $signal  = 'BUY';
            $reasons = [
                'EMA 9/21 bullish crossover',
                'RSI ' . round($rsi, 1) . ' in range',
                'MACD histogram rising',
                'Low volume (weak signal)',
            ];
        } elseif ($bearishCrossover) {
            $signal  = 'SELL';
            $reasons = ['EMA 9/21 bearish crossover'];
        }

        return [
            'signal'    => $signal,
            'reasons'   => $reasons,
            'ema9'      => $lastEma9,
            'ema21'     => $lastEma21,
            'rsi'       => round($rsi, 2),
            'macd_hist' => round($lastHistogram, 8),
            'atr'       => $atr,
            'volume_ok' => $volOk,
        ];
    }
}
