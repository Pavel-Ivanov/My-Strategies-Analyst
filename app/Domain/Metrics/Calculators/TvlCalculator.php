<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class TvlCalculator implements MetricCalculatorInterface
{
    private array $config = [];

    public function key(): string
    {
        return 'tvl';
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDescription(): string
    {
        return 'Total Value Locked (TVL) in USD';
    }

    public function getUnit(): string
    {
        return 'USD';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        // Берём последний Snapshot на/до указанного момента
        $snapshot = $strategy->snapshots()
            ->where('snapshot_at', '<=', $at)
            ->orderBy('snapshot_at', 'desc')
            ->first();

        if (! $snapshot) {
            return new MetricResult(
                key: $this->key(),
                value: null,
                unit: 'USD',
                displayName: 'TVL',
                meta: [
                    'window' => 'point',
                    'to' => $at->toIso8601String(),
                    'inclusive' => true,
                    'source' => 'snapshots.total_liquidity + snapshots.fees_uncollected',
                    'reason' => 'no_snapshot_before_or_at_to',
                ]
            );
        }

        $value = (float) (($snapshot->total_liquidity ?? 0) + ($snapshot->fees_uncollected ?? 0));

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: 'USD',
            displayName: 'TVL',
            meta: [
                'window' => 'point',
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'snapshot_id' => $snapshot->id,
                'snapshot_at' => $snapshot->snapshot_at ?? null,
                'components' => [
                    'total_liquidity' => (float) ($snapshot->total_liquidity ?? 0),
                    'fees_uncollected' => (float) ($snapshot->fees_uncollected ?? 0),
                ],
                'formula' => 'tvl = total_liquidity + fees_uncollected',
            ]
        );
    }
}
