<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class LoanInterestAccruedTotalCalculator implements MetricCalculatorInterface
{
    private array $config = [];

    public function key(): string
    {
        return 'loan_interest_accrued_total';
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDescription(): string
    {
        return 'Total loan interest accrued amount in USD';
    }

    public function getUnit(): string
    {
        return 'USD';
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        $value = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::LOAN_INTEREST_ACCRUED)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: 'USD',
            displayName: 'Loan Interest Accrued Total',
            meta: [
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'source' => 'transactions.total_value',
                'transaction_type' => TransactionType::LOAN_INTEREST_ACCRUED->value,
            ],
        );
    }
}
