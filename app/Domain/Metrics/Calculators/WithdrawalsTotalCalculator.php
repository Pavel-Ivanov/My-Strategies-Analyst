<?php

namespace App\Domain\Metrics\Calculators;

use App\Domain\Metrics\MetricCalculatorInterface;
use App\Domain\Metrics\MetricResult;
use App\Enums\TransactionType;
use App\Models\Strategy;
use Carbon\CarbonInterface;

class WithdrawalsTotalCalculator implements MetricCalculatorInterface
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
        return 'withdrawals_total';
    }

    public function setConfig(array $config): void
    {
        $allowed = ['unit', 'round', 'mode'];
        $this->config = array_replace($this->config, array_intersect_key($config, array_flip($allowed)));
    }

    public function getDescription(): string
    {
        return 'Total withdrawals amount in USD';
    }

    public function getUnit(): string
    {
        return (string) ($this->config['unit'] ?? 'USD');
    }

    public function calculate(Strategy $strategy, CarbonInterface $at): MetricResult
    {
        $value = (float) $strategy->transactions()
            ->where('transaction_type', TransactionType::WITHDRAW)
            ->where('transaction_date', '<=', $at)
            ->sum('total_value');

        $round = $this->config['round'];
        if (is_int($round)) {
            $value = round($value, $round);
        }

        return new MetricResult(
            key: $this->key(),
            value: $value,
            unit: (string) ($this->config['unit'] ?? 'USD'),
            displayName: 'Total Withdrawals (LTD)',
            meta: [
                'window' => 'ltd',
                'to' => $at->toIso8601String(),
                'inclusive' => true,
                'source' => 'transactions.total_value',
                'transaction_type' => TransactionType::WITHDRAW->value,
                'applied_config' => [
                    'unit' => $this->config['unit'] ?? 'USD',
                    'round' => $round,
                    'mode' => 'ltd',
                ],
            ],
        );
    }
}
