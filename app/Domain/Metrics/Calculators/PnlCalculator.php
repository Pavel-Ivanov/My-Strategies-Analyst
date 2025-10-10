<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class PnlCalculator implements MetricCalculatorInterface
{
    private array $config = [];

    public function key(): string
    {
        return 'pnl';
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDescription(): string
    {
        return 'Profit and Loss (PNL) calculation';
    }

    public function getUnit(): string
    {
        return 'USD';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        // TVL(at)
        $snapshot = $strategy->snapshots()
            ->where('snapshot_at', '<=', $at)
            ->orderBy('snapshot_at', 'desc')
            ->first();

        if (! $snapshot) {
            return new MetricResult(
                key: $this->key(),
                value: null,
                unit: 'USD',
                displayName: 'PNL',
                meta: [
                    'window' => 'ltd',
                    'to' => $at->toIso8601String(),
                    'inclusive' => true,
                    'reason' => 'no_snapshot_before_or_at_to',
                ]
            );
        }

        $tvl = (float) (($snapshot->total_liquidity ?? 0) + ($snapshot->fees_uncollected ?? 0));

        // Sums up to and including $at
        $deposits = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::DEPOSIT)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        $withdrawals = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::WITHDRAW)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        $feesCollected = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::COLLECT_FEES)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        // PNL(at) = TVL(at) + FeesCollected(to<=at) - NetInvested(to<=at)
        $netInvested = $deposits - $withdrawals; // Net Capital In
        $pnlUsd = $tvl + $feesCollected - $netInvested;

        return new MetricResult(
            key: $this->key(),
            value: $pnlUsd,
            unit: 'USD',
            displayName: 'PNL',
            meta: [
                'window' => 'ltd',
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'components' => [
                    'tvl' => $tvl,
                    'fees_collected' => $feesCollected,
                    'deposits_total' => $deposits,
                    'withdrawals_total' => $withdrawals,
                    'net_invested' => $netInvested,
                ],
                'formula' => 'pnl = tvl + fees_collected - (deposits - withdrawals)',
            ]
        );
    }
}
