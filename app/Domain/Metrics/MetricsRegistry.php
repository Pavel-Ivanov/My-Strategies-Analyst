<?php

namespace App\Domain\Metrics;

use App\Models\Strategy;
use Carbon\CarbonInterface;

class MetricsRegistry
{
    /** @var MetricCalculatorInterface[] keyed by metric key */
    protected array $calculators = [];

    /** @param iterable<MetricCalculatorInterface> $calculators */
    public function __construct(iterable $calculators = [])
    {
        foreach ($calculators as $calc) {
            $this->register($calc);
        }
    }

    public function register(MetricCalculatorInterface $calculator): void
    {
        $this->calculators[$calculator->key()] = $calculator;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->calculators);
    }

    public function keys(): array
    {
        return array_keys($this->calculators);
    }

    public function get(string $key): ?MetricCalculatorInterface
    {
        return $this->calculators[$key] ?? null;
    }

    /**
     * Calculate all registered metrics.
     *
     * @return array<string, MetricResult>
     */
    public function calculateAll(Strategy $strategy, CarbonInterface $at): array
    {
        return $this->calculateFor($strategy, $at, array_keys($this->calculators));
    }

    /**
     * Calculate only selected metric keys (unknown keys are ignored).
     *
     * @param  string[]  $keys
     * @return array<string, MetricResult>
     */
    public function calculateFor(Strategy $strategy, CarbonInterface $at, array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            if (! isset($this->calculators[$key])) {
                continue;
            }
            $results[$key] = $this->calculators[$key]->calculate($strategy, $at);
        }

        return $results;
    }

    /**
     * Get all calculators for form options (class => description).
     *
     * @return array<string, string>
     */
    public function getCalculatorOptions(): array
    {
        $options = [];
        foreach ($this->calculators as $calculator) {
            $options[get_class($calculator)] = $calculator->getDescription();
        }

        return $options;
    }

    /**
     * Получить ключ метрики по полному имени класса калькулятора.
     */
    public function getKeyByClass(string $class): ?string
    {
        foreach ($this->calculators as $calculator) {
            if (get_class($calculator) === $class) {
                return $calculator->key();
            }
        }

        return null;
    }

    /**
     * Return all available metrics keyed by metric key with label, unit, and description.
     *
     * @return array<string, array{label:string,unit:string,description:string}>
     */
    public function listAll(): array
    {
        $result = [];
        foreach ($this->calculators as $key => $calc) {
            $result[$key] = [
                'label' => ucwords(str_replace('_', ' ', (string) $key)),
                'unit' => $calc->getUnit(),
                'description' => $calc->getDescription(),
            ];
        }
        ksort($result);

        return $result;
    }
}
