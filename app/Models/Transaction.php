<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Jobs\CalculateStrategyMetricsJob;
use App\Services\StrategyMetricsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected static function booted(): void
    {
        static::created(function (Transaction $tx) {
            // Триггер пересчёта метрик по транзакциям по умолчанию отключён.
            if (! config('metrics.trigger_on_transaction', false)) {
                return;
            }

            $at = $tx->transaction_date ? Carbon::parse($tx->transaction_date) : ($tx->created_at ?? Carbon::now());
            if ($tx->strategy_id) {
                if (config('metrics.sync_on_transaction', false)) {
                    // Синхронный пересчёт (если включено)
                    $service = app()->make(StrategyMetricsService::class);
                    $service->snapshot($tx->strategy, $at);
                } else {
                    // Асинхронно через очередь
                    CalculateStrategyMetricsJob::dispatch($tx->strategy_id, $at);
                }
            }
        });
    }

    /*    protected $fillable = [
            'user_id',
            'strategy_id',
            'status',
            'start_at',
            ];*/
    protected $guarded = [];

    protected $casts = [
        'transaction_type' => TransactionType::class,
    ];

    public function strategy()
    {
        return $this->belongsTo(Strategy::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_transaction', 'transaction_id', 'asset_id');
    }

    public function transactionAssets(): HasMany
    {
        return $this->hasMany(AssetTransaction::class);
    }

    public function getAssetsWithValues()
    {
        return $this->transactionAssets()
            ->with('asset')
            ->get()
            ->map(function ($assetTransaction) {
                return [
                    'asset' => $assetTransaction->asset,
                    'amount' => $assetTransaction->asset_amount,
                    'price' => $assetTransaction->asset_price,
                    'value' => $assetTransaction->asset_amount * $assetTransaction->asset_price,
                ];
            })
            ->toArray();
    }

    public function durationFromToday(): string
    {
        $transactionDate = Carbon::parse($this->transaction_date);
        $now = Carbon::now();

        if ($transactionDate->isFuture()) {
            return $transactionDate->diffForHumans(['parts' => 2]);
        }

        return $transactionDate->diffForHumans($now, ['parts' => 2]);
    }
}
