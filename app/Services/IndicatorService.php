<?php

namespace App\Services;

class IndicatorService
{
    public function ema(array $closes, int $period): array
    {
        if (count($closes) < $period) return [];

        $k   = 2 / ($period + 1);
        $ema = [array_sum(array_slice($closes, 0, $period)) / $period];

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

        $gains   = array_map(fn($c) => max($c, 0), $changes);
        $losses  = array_map(fn($c) => abs(min($c, 0)), $changes);
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        for ($i = $period; $i < count($changes); $i++) {
            $avgGain = ($avgGain * ($period - 1) + $gains[$i]) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $losses[$i]) / $period;
        }

        if ($avgLoss == 0) return 100.0;
        return 100 - (100 / (1 + $avgGain / $avgLoss));
    }

    public function macd(array $closes, int $fast = 12, int $slow = 26, int $signal = 9): array
    {
        $emaFast = $this->ema($closes, $fast);
        $emaSlow = $this->ema($closes, $slow);

        $offset   = $slow - $fast;
        $macdLine = [];
        for ($i = 0; $i < count($emaSlow); $i++) {
            $macdLine[] = $emaFast[$i + $offset] - $emaSlow[$i];
        }

        $signalLine = $this->ema($macdLine, $signal);
        $offsetSig  = count($macdLine) - count($signalLine);
        $histogram  = [];
        for ($i = 0; $i < count($signalLine); $i++) {
            $histogram[] = $macdLine[$i + $offsetSig] - $signalLine[$i];
        }

        return ['macd' => $macdLine, 'signal' => $signalLine, 'histogram' => $histogram];
    }

    // Bollinger Bands: upper, middle (SMA), lower, and %B position (0=lower, 1=upper)
    public function bollingerBands(array $closes, int $period = 20, float $mult = 2.0): array
    {
        if (count($closes) < $period) {
            return ['upper' => 0, 'middle' => 0, 'lower' => 0, 'pct_b' => 0.5, 'squeeze' => false];
        }

        $slice  = array_slice($closes, -$period);
        $middle = array_sum($slice) / $period;
        $variance = array_sum(array_map(fn($c) => ($c - $middle) ** 2, $slice)) / $period;
        $std    = sqrt($variance);

        $upper  = $middle + $mult * $std;
        $lower  = $middle - $mult * $std;
        $last   = end($closes);
        $pctB   = ($upper - $lower) > 0 ? ($last - $lower) / ($upper - $lower) : 0.5;

        // Squeeze: bands narrower than 2% of price (low volatility — breakout incoming)
        $squeeze = $std > 0 && ($upper - $lower) / $middle < 0.02;

        return [
            'upper'   => $upper,
            'middle'  => $middle,
            'lower'   => $lower,
            'pct_b'   => round($pctB, 4),   // 0 = at lower, 0.5 = middle, 1 = at upper
            'squeeze' => $squeeze,
        ];
    }

    // Stochastic RSI: %K and %D (0-100), more sensitive than regular RSI
    public function stochRsi(array $closes, int $rsiPeriod = 14, int $stochPeriod = 14, int $kSmooth = 3, int $dSmooth = 3): array
    {
        $rsiValues = [];
        for ($i = $rsiPeriod; $i <= count($closes); $i++) {
            $rsiValues[] = $this->rsi(array_slice($closes, 0, $i), $rsiPeriod);
        }

        if (count($rsiValues) < $stochPeriod) {
            return ['k' => 50.0, 'd' => 50.0];
        }

        $stochK = [];
        for ($i = $stochPeriod - 1; $i < count($rsiValues); $i++) {
            $slice   = array_slice($rsiValues, $i - $stochPeriod + 1, $stochPeriod);
            $minRsi  = min($slice);
            $maxRsi  = max($slice);
            $stochK[] = $maxRsi > $minRsi ? (($rsiValues[$i] - $minRsi) / ($maxRsi - $minRsi)) * 100 : 50;
        }

        // Smooth %K and compute %D
        $smoothK = $this->simpleMovingAvg($stochK, $kSmooth);
        $smoothD = $this->simpleMovingAvg($smoothK, $dSmooth);

        return [
            'k' => round(end($smoothK) ?: 50.0, 2),
            'd' => round(end($smoothD) ?: 50.0, 2),
        ];
    }

    private function simpleMovingAvg(array $values, int $period): array
    {
        if (count($values) < $period) return $values;
        $result = [];
        for ($i = $period - 1; $i < count($values); $i++) {
            $result[] = array_sum(array_slice($values, $i - $period + 1, $period)) / $period;
        }
        return $result;
    }

    // ATR: measures real volatility per candle (Wilder smoothing)
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

        $atr = array_sum(array_slice($trueRanges, 0, $period)) / $period;
        for ($i = $period; $i < count($trueRanges); $i++) {
            $atr = ($atr * ($period - 1) + $trueRanges[$i]) / $period;
        }

        return $atr;
    }

    // Volume spike: current candle volume vs. recent average
    public function volumeSpike(array $klines, int $period = 20, float $multiplier = 1.5): bool
    {
        if (count($klines) < $period + 1) return false;

        $volumes    = array_column($klines, 'volume');
        $lastVolume = end($volumes);
        $avgVolume  = array_sum(array_slice($volumes, -($period + 1), $period)) / $period;

        return $avgVolume > 0 && $lastVolume >= $avgVolume * $multiplier;
    }

    // Higher-timeframe trend: BULL if EMA9 > EMA21 and price above both
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
        $closes = array_column($klines, 'close');

        $ema9   = $this->ema($closes, 9);
        $ema21  = $this->ema($closes, 21);
        $ema50  = $this->ema($closes, 50);
        $rsi    = $this->rsi($closes, 14);
        $macd   = $this->macd($closes);
        $bb     = $this->bollingerBands($closes, 20, 2.0);
        $stoch  = $this->stochRsi($closes, 14, 14, 3, 3);
        $atr    = $this->atr($klines, 14);
        $volOk  = $this->volumeSpike($klines, 20, 1.5);

        $lastEma9  = end($ema9);
        $prevEma9  = $ema9[count($ema9) - 2] ?? $lastEma9;
        $lastEma21 = end($ema21);
        $prevEma21 = $ema21[count($ema21) - 2] ?? $lastEma21;
        $lastEma50 = end($ema50) ?: 0;
        $lastClose = end($closes);

        $lastHistogram = end($macd['histogram']) ?: 0;
        $prevHistogram = $macd['histogram'][count($macd['histogram']) - 2] ?? 0;

        $bullishCrossover = $prevEma9 <= $prevEma21 && $lastEma9 > $lastEma21;
        $bearishCrossover = $prevEma9 >= $prevEma21 && $lastEma9 < $lastEma21;

        // Refined filters
        $rsiOk      = $rsi >= 35 && $rsi <= 68;                          // not overbought
        $macdBull   = $lastHistogram > 0 && $lastHistogram > $prevHistogram;
        $aboveEma50 = $lastEma50 > 0 && $lastClose > $lastEma50;          // medium-term uptrend
        $bbOk       = $bb['pct_b'] < 0.85;                               // not near upper band
        $stochOk    = $stoch['k'] > 20 && $stoch['k'] < 80;              // not extreme levels
        $stochBull  = $stoch['k'] > $stoch['d'];                          // %K above %D (momentum up)

        $signal  = 'HOLD';
        $reasons = [];

        $buyConditions = $bullishCrossover && $rsiOk && $macdBull && $aboveEma50 && $bbOk && $stochOk && $stochBull;

        if ($buyConditions) {
            $signal  = 'BUY';
            $reasons = [
                'EMA 9/21 bullish crossover',
                'RSI ' . round($rsi, 1) . ' in range',
                'MACD histogram rising',
                'Price above EMA50',
                'BB %B=' . round($bb['pct_b'] * 100, 0) . '% (not overbought)',
                'StochRSI K=' . $stoch['k'] . ' bullish',
                $volOk ? 'Volume spike confirmed' : 'Low volume (weak signal)',
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
            'ema50'     => $lastEma50,
            'rsi'       => round($rsi, 2),
            'macd_hist' => round($lastHistogram, 8),
            'bb_pct_b'  => $bb['pct_b'],
            'bb_squeeze'=> $bb['squeeze'],
            'stoch_k'   => $stoch['k'],
            'stoch_d'   => $stoch['d'],
            'atr'       => $atr,
            'volume_ok' => $volOk,
        ];
    }
}
