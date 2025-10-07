<?php

namespace App\Models;

use App\Jobs\CalculateStrategyMetricsJob;
use App\Services\StrategyMetricsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snapshot extends Model
{
    protected static function booted(): void
    {
        // Триггеры пересчёта на событиях Snapshot включаются только если явно активированы в конфиге
        if (config('metrics.trigger_on_snapshot', false)) {
            static::created(function (Snapshot $snapshot) {
                $at = $snapshot->snapshot_at ? Carbon::parse($snapshot->snapshot_at) : ($snapshot->created_at ?? Carbon::now());

                if (config('metrics.sync_on_snapshot', false)) {
                    // Синхронный пересчёт метрик при создании снапшота
                    $service = app()->make(StrategyMetricsService::class);
                    $service->snapshot($snapshot->strategy, $at);
                } else {
                    // Асинхронно через очередь (как раньше)
                    CalculateStrategyMetricsJob::dispatch($snapshot->strategy_id, $at);
                }
            });

            static::updated(function (Snapshot $snapshot) {
                // Пересчитываем метрики и при редактировании снапшота
                $at = $snapshot->snapshot_at ? Carbon::parse($snapshot->snapshot_at) : ($snapshot->updated_at ?? Carbon::now());

                if (config('metrics.sync_on_snapshot', false)) {
                    $service = app()->make(StrategyMetricsService::class);
                    $service->snapshot($snapshot->strategy, $at);
                } else {
                    CalculateStrategyMetricsJob::dispatch($snapshot->strategy_id, $at);
                }
            });
        }
    }

    protected $fillable = [
        'strategy_id',
        'snapshot_at',
        'total_liquidity',
        'fees_uncollected',
        'current_loan_balance',
        'accrued_loan_interest',
        'overall_health_factor',
    ];

    protected $casts = [
        // no metric-related casts left; meta removed
    ];

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_snapshot', 'snapshot_id', 'asset_id');
    }

    public function snapshotAssets(): HasMany
    {
        return $this->hasMany(AssetSnapshot::class);
    }

    public function getAssetsWithValues()
    {
        return $this->snapshotAssets()
            ->with('asset')
            ->get()
            ->map(function ($assetSnapshot) {
                return [
                    'asset' => $assetSnapshot->asset,
                    'amount' => $assetSnapshot->asset_amount,
                    'price' => $assetSnapshot->asset_price,
                    'value' => $assetSnapshot->asset_amount * $assetSnapshot->asset_price,
                ];
            })
            ->toArray();
    }
}
