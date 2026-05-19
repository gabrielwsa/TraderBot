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

    public function analyze(array $klines): array
    {
        $closes = array_column($klines, 'close');

        $ema9  = $this->ema($closes, 9);
        $ema21 = $this->ema($closes, 21);
        $rsi   = $this->rsi($closes, 14);
        $macd  = $this->macd($closes);

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

        $signal = 'HOLD';
        $reasons = [];

        if ($bullishCrossover && $rsiOk && $macdBull) {
            $signal = 'BUY';
            $reasons = [
                "EMA 9/21 bullish crossover",
                "RSI " . round($rsi, 1) . " in range",
                "MACD histogram rising",
            ];
        } elseif ($bearishCrossover) {
            $signal = 'SELL';
            $reasons = ["EMA 9/21 bearish crossover"];
        }

        return [
            'signal'    => $signal,
            'reasons'   => $reasons,
            'ema9'      => $lastEma9,
            'ema21'     => $lastEma21,
            'rsi'       => round($rsi, 2),
            'macd_hist' => round($lastHistogram, 8),
        ];
    }
}
