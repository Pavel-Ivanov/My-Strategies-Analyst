<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * APR (Farming, LTD) calculator.
 *
 * Computes annualized percentage rate for farming strategies since inception (LTD).
 */
class AprFarmingCalculator implements MetricCalculatorInterface
{
    /**
     * Safe config for LTD mode.
     * - include_il: whether to include impermanent loss component (placeholder for now).
     * - unit: display unit for the metric value.
     * - round: optional number of decimals to round the resulting value to.
     */
    private array $config = [
        'include_il' => true,
        'unit' => '%',
        'round' => null,
        'mode' => 'ltd',
    ];

    public function key(): string
    {
        // Required identifier for this calculator
        return 'apr-farming';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        // Inception date for LTD window
        $from = $this->detectInception($strategy, $at);

        if ($from->gt($at)) {
            $from = Carbon::parse($at)->copy()->subDay();
        }

        // Rewards and impermanent loss over LTD window
        $rewards = $this->getRewardsValue($strategy, $from, $at);
        $impermanentLoss = $this->config['include_il'] ? $this->getImpermanentLoss($strategy, $from, $at) : 0.0;

        // Investment baseline. Keep behavior similar to previous implementation: sum of deposits up to $at
        $initialInvestment = $this->getInitialInvestment($strategy, $at);

        $days = max(1, $from->diffInDays($at));

        $apr = null;
        $totalReturn = null;
        if ($initialInvestment > 0) {
            $totalReturn = $rewards + $impermanentLoss;
            $apr = ($totalReturn / $initialInvestment) * (365 / $days) * 100;
        }

        $value = $apr;
        $round = $this->config['round'];
        if ($value !== null && is_int($round)) {
            $value = round($value, $round);
        }

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: (string) ($this->config['unit'] ?? '%'),
            displayName: 'APR (Farming, LTD)',
            meta: [
                'calculator_type' => 'farming',
                'window' => 'ltd',
                'from' => $from->toIso8601String(),
                'to' => $at->toIso8601String(),
                'days' => $days,
                'rewards' => $rewards,
                'impermanent_loss' => $impermanentLoss,
                'initial_investment' => $initialInvestment,
                'total_return' => $totalReturn,
                'applied_config' => [
                    'include_il' => (bool) ($this->config['include_il'] ?? true),
                    'unit' => $this->config['unit'] ?? '%',
                    'round' => $round,
                    'mode' => 'ltd',
                ],
                'formula' => 'apr_ltd = (rewards + il) / initial_investment * (365 / days) * 100',
            ]
        );
    }

    public function setConfig(array $config): void
    {
        // whitelist safe keys
        $allowed = ['include_il', 'unit', 'round', 'mode'];
        $this->config = array_replace($this->config, array_intersect_key($config, array_flip($allowed)));
    }

    public function getDescription(): string
    {
        return 'Annual Percentage Rate (LTD) for farming strategies based on rewards and optional impermanent loss since inception';
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

    private function getRewardsValue(Strategy $strategy, CarbonInterface $from, CarbonInterface $to): float
    {
        // For farming strategies, rewards come from COLLECT_FEES transactions
        return (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::COLLECT_FEES)
            ->where('transaction_date', '>=', $from)
            ->where('transaction_date', '<=', $to)
            ->sum('total_value');
    }

    private function getImpermanentLoss(Strategy $strategy, CarbonInterface $from, CarbonInterface $to): float
    {
        // Placeholder: IL calculation not implemented yet
        return 0.0;
    }

    private function getInitialInvestment(Strategy $strategy, CarbonInterface $to): float
    {
        // Sum of deposits up to the point in time
        return (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::DEPOSIT)
            ->where('transaction_date', '<=', $to)
            ->sum('total_value');
    }
}
