<?php

namespace App\Jobs;

use App\Models\Strategy;
use App\Services\StrategyMetricsService;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateStrategyMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $strategyId, public CarbonInterface $at) {}

    public function handle(StrategyMetricsService $service): void
    {
        $strategy = Strategy::find($this->strategyId);
        if (! $strategy) {
            return;
        }
        $service->snapshot($strategy, $this->at);
    }
}
