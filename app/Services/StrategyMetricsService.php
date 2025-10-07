<?php

namespace App\Services;

use App\Domain\Metrics\MetricsRegistry;
use App\Models\Strategy;
use App\Models\StrategyMetricResult;
use Carbon\CarbonInterface;

class StrategyMetricsService
{
    public function __construct(private readonly MetricsRegistry $registry) {}

    /**
     * Calculate and upsert metrics for a strategy at the given time $at.
     * Uses per-strategy configuration from strategy_strategy_metric (metric_key, is_enabled, custom_config)
     * and MetricsRegistry to resolve calculators, as described in the documentation.
     */
    public function snapshot(Strategy $strategy, CarbonInterface $at): void
    {
        $results = $this->calculateMetrics($strategy, $at);

        // Remove previously calculated values for this strategy and timestamp before saving new ones
        StrategyMetricResult::query()
            ->where('strategy_id', $strategy->id)
            ->where('snapshot_at', $at)
            ->delete();

        foreach ($results as $result) {
            StrategyMetricResult::query()->updateOrCreate(
                [
                    'strategy_id' => $strategy->id,
                    'metric_key' => $result->key,
                    'snapshot_at' => $at,
                ],
                [
                    'value' => $result->value,
                    'unit' => $result->unit,
                    'meta' => $result->meta,
                ]
            );
        }
    }

    /**
     * Calculate metrics using new architecture (v2.0) - individual strategy metrics configuration.
     */
    public function calculateMetrics(Strategy $strategy, ?CarbonInterface $at = null): \Illuminate\Support\Collection
    {
        $at = $at ?? now();
        $results = collect();

        // Get configured metrics for the strategy (pivot rows)
        $strategyMetrics = $strategy->strategyMetrics()
            ->where('is_enabled', true)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id')
            ->get();

        foreach ($strategyMetrics as $cfg) {
            $calculator = $this->registry->get($cfg->metric_key);
            if (! $calculator) {
                logger()->warning("No calculator found for metric key {$cfg->metric_key}", [
                    'strategy_id' => $strategy->id,
                    'metric_key' => $cfg->metric_key,
                ]);

                continue;
            }

            // Pass custom configuration to calculator
            if (! empty($cfg->custom_config)) {
                $calculator->setConfig($cfg->custom_config);
            }

            try {
                $result = $calculator->calculate($strategy, $at);
                $results->put($cfg->metric_key, $result);
            } catch (\Exception $e) {
                // Log error but continue with other metrics
                logger()->error("Failed to calculate metric {$cfg->metric_key} for strategy {$strategy->id}", [
                    'exception' => $e->getMessage(),
                    'strategy_id' => $strategy->id,
                    'metric_key' => $cfg->metric_key,
                ]);
            }
        }

        return $results;
    }
}
