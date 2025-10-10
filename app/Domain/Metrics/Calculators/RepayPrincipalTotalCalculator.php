<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class RepayPrincipalTotalCalculator implements MetricCalculatorInterface
{
    /**
     * LTD policy: expose only safe display/config options.
     */
    private array $config = [
        'unit' => 'USD',
        'round' => null,
        'mode' => 'ltd',
    ];

    public function key(): string
    {
        return 'repay_principal_total';
    }

    public function setConfig(array $config): void
    {
        $allowed = ['unit', 'round', 'mode'];
        $this->config = array_replace($this->config, array_intersect_key($config, array_flip($allowed)));
    }

    public function getDescription(): string
    {
        return 'Total repaid principal amount in USD';
    }

    public function getUnit(): string
    {
        return 'USD';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        $value = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::REPAY_PRINCIPAL)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: 'USD',
            displayName: 'Repay Principal Total',
            meta: [
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'source' => 'transactions.total_value',
                'transaction_type' => TransactionType::REPAY_PRINCIPAL->value,
            ],
        );
    }
}
