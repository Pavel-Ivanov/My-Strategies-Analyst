<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class RoiCalculator implements MetricCalculatorInterface
{
    private array $config = [];

    public function key(): string
    {
        return 'roi';
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDescription(): string
    {
        return 'Return on Investment (ROI) percentage';
    }

    public function getUnit(): string
    {
        return '%';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        // Compute PNL and Net Invested similarly to PnlCalculator
        $snapshot = $strategy->snapshots()
            ->where('snapshot_at', '<=', $at)
            ->orderBy('snapshot_at', 'desc')
            ->first();

        if (! $snapshot) {
            return new MetricResult(
                key: $this->key(),
                value: null,
                unit: '%',
                displayName: 'ROI',
                meta: [
                    'window' => 'ltd',
                    'to' => $at->toIso8601String(),
                    'inclusive' => true,
                    'reason' => 'no_snapshot_before_or_at_to',
                ]
            );
        }

        $tvl = (float) (($snapshot->total_liquidity ?? 0) + ($snapshot->fees_uncollected ?? 0));

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

        $netInvested = $deposits - $withdrawals;
        $pnlUsd = $tvl + $feesCollected - $netInvested;

        $roi = null;
        if ($netInvested > 0) {
            $roi = ($pnlUsd / $netInvested) * 100.0;
        }

        return new MetricResult(
            key: $this->key(),
            value: $roi,
            unit: '%',
            displayName: 'ROI',
            meta: [
                'window' => 'ltd',
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'components' => [
                    'pnl_usd' => $pnlUsd,
                    'net_invested' => $netInvested,
                    'tvl' => $tvl,
                    'fees_collected' => $feesCollected,
                    'deposits_total' => $deposits,
                    'withdrawals_total' => $withdrawals,
                ],
                'formula' => 'roi% = pnl / (deposits - withdrawals) * 100',
                'guard' => 'returns null when net_invested <= 0',
            ]
        );
    }
}
