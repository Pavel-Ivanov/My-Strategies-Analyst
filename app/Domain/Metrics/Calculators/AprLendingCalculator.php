<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class AprLendingCalculator implements MetricCalculatorInterface
{
    /**
     * LTD policy: only safe display/config options.
     * unit: string (default '%'), round: int|null (decimals) for formatting value.
     */
    private array $config = [
        'unit' => '%',
        'round' => null,
        'include_pending' => false,
        'mode' => 'ltd',
    ];

    public function key(): string
    {
        return 'apr-lending';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        // Determine inception (from) date: earliest snapshot_at, else earliest transaction_date, else created_at
        $from = $this->detectInception($strategy, $at);

        // Guard: if from is after to, normalize to to - 1 day
        if ($from->gt($at)) {
            $from = Carbon::parse($at)->copy()->subDay();
        }

        // APR(LTD) = collected_fees_since_inception / avg_liquidity_since_inception * (365 / days_since_inception) * 100
        $fees = $this->getCollectedFees($strategy, $from, $at, (bool) ($this->config['include_pending'] ?? false));
        $avgLiquidity = $this->getAverageLiquidity($strategy, $from, $at);

        $days = max(1, $from->diffInDays($at));
        $apr = null;
        if ($avgLiquidity > 0) {
            $apr = ($fees / $avgLiquidity) * (365 / $days) * 100;
        }

        // Optional rounding for presentation
        $value = $apr;
        $round = $this->config['round'];
        if ($value !== null && is_int($round)) {
            $value = round($value, $round);
        }

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: (string) ($this->config['unit'] ?? '%'),
            displayName: 'APR (Lending, LTD)',
            meta: [
                'calculator_type' => 'lending',
                'window' => 'ltd',
                'from' => $from->toIso8601String(),
                'to' => $at->toIso8601String(),
                'days' => $days,
                'fees' => $fees,
                'avg_liquidity' => $avgLiquidity,
                'formula' => 'apr_ltd = (fees / avg_liquidity) * (365 / days) * 100',
                'applied_config' => [
                    'unit' => $this->config['unit'] ?? '%',
                    'round' => $round,
                    'include_pending' => (bool) ($this->config['include_pending'] ?? false),
                    'mode' => 'ltd',
                ],
            ]
        );
    }

    public function setConfig(array $config): void
    {
        // whitelist only safe keys for LTD mode
        $allowed = ['unit', 'round', 'include_pending', 'mode'];
        $this->config = array_replace($this->config, array_intersect_key($config, array_flip($allowed)));
    }

    public function getDescription(): string
    {
        return 'Annual Percentage Rate (LTD) for lending strategies based on collected fees and average liquidity since inception';
    }

    public function getUnit(): string
    {
        return (string) ($this->config['unit'] ?? '%');
    }

    private function detectInception(Strategy $strategy, CarbonInterface $at): Carbon
    {
        // Prefer earliest snapshot_at <= $at
        $snapAt = $strategy->snapshots()
            ->where('snapshot_at', '<=', $at)
            ->min('snapshot_at');
        if ($snapAt) {
            return Carbon::parse($snapAt);
        }

        // Fallback: earliest transaction_date <= $at
        $txAt = $strategy->transactions()
            ->where('transaction_date', '<=', $at)
            ->min('transaction_date');
        if ($txAt) {
            return Carbon::parse($txAt);
        }

        // Last resort: strategy created_at
        return Carbon::parse($strategy->created_at ?? $at)->copy();
    }

    private function getCollectedFees(Strategy $strategy, CarbonInterface $from, CarbonInterface $to, bool $includePending = false): float
    {
        $query = $strategy->transactions()
            ->where('transaction_type', TransactionType::COLLECT_FEES)
            ->where('transaction_date', '>=', $from)
            ->where('transaction_date', '<=', $to);

        if (! $includePending) {
            // If there is a status column in transactions, filter to confirmed here.
            // $query->where('status', 'confirmed');
        }

        return (float) $query->sum('total_value');
    }

    private function getAverageLiquidity(Strategy $strategy, CarbonInterface $from, CarbonInterface $to): float
    {
        $startLiquidity = $strategy->getTotalValue(Carbon::parse($from));
        $endLiquidity = $strategy->getTotalValue(Carbon::parse($to));

        return ($startLiquidity + $endLiquidity) / 2;
    }
}
