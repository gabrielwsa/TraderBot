<?php

namespace App\Contracts;

interface AiProviderInterface
{
    /**
     * Analyze a trading signal and return a decision.
     *
     * @param  array  $data  Indicators, trend, pair info
     * @return array{decision: string, reason: string, confidence: int}
     *               decision: CONFIRM | REJECT | NEUTRAL
     *               confidence: 0-100
     */
    public function analyze(array $data): array;

    public function isAvailable(): bool;
}
