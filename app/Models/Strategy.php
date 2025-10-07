<?php

namespace App\Models;

use App\Enums\StrategyStatus;
use App\Enums\StrategyType;
use App\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Strategy extends Model
{
    /**
     * Cached latest metrics per timestamp key (stringified) to avoid N+1 inside one request.
     *
     * @var array<string, array<string, \App\Models\StrategyMetricResult>>
     */
    protected array $latestMetricsCache = [];

    protected $fillable = [
        'status',
        'type',
        'name',
        'description',
        'strategy_url',
        'resource_id',
        'chain_id',
        'wallet_id',
        'wallet_address',
        'start_at',
        'finish_at',
    ];

    protected $casts = [
        'status' => StrategyStatus::class,
        'type' => StrategyType::class,
        'start_at' => 'datetime',
        'finish_at' => 'datetime',
        'initial_deposit' => 'decimal:8',
    ];

    // Relationships
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo(Chain::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_strategy', 'strategy_id', 'asset_id')
            ->orderBy('order');
    }

    public function strategyAssets(): HasMany
    {
        return $this->hasMany(AssetStrategy::class);
    }

    /**
     * The parameters that belong to the strategy.
     */
    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(StrategyParameter::class, 'strategy_strategy_parameter')
            ->using(StrategyStrategyParameter::class)
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Get the strategy parameter relationships.
     */
    public function strategyParameters(): HasMany
    {
        return $this->hasMany(StrategyStrategyParameter::class);
    }

    /**
     * Get the strategy parameter relationships.
     */
    public function strategyMetrics(): HasMany
    {
        return $this->hasMany(StrategyStrategyMetric::class);
    }

    // Mutators
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFinished($query)
    {
        return $query->whereNotNull('finish_at');
    }

    // Custom Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function duration()
    {
        if ($this->finish_at) {
            return (int) $this->start_at->diffInDays($this->finish_at);
        }

        return (int) $this->start_at->diffInDays(now());
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionsByType(?TransactionType $type = null)
    {
        $query = $this->hasMany(Transaction::class);

        if ($type !== null) {
            $query->where('transaction_type', $type);
        }

        return $query;
    }

    public function getTransactionsAmountAtDate(TransactionType $type, ?Carbon $date = null)
    {
        $query = $this->transactionsByType($type);

        if ($date !== null) {
            $query->where('transaction_date', '<=', $date);
        }

        return $query->sum('total_value');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(StrategyMetricResult::class);
    }

    /**
     * Последние строки метрик (все ключи) на момент последнего snapshot_at по стратегии.
     * Реализовано как отношение hasMany с присоединением подзапроса (strategy_id, max(snapshot_at)).
     * Можно предзагрузить через with('latestMetricsRows').
     */
    public function latestMetricsRows(): HasMany
    {
        $sub = DB::table('strategy_metric_results as sm2')
            ->selectRaw('sm2.strategy_id, MAX(sm2.snapshot_at) as max_at')
            ->groupBy('sm2.strategy_id');

        return $this->hasMany(StrategyMetricResult::class, 'strategy_id', 'id')
            ->joinSub($sub, 'lm', function ($join) {
                $join->on('strategy_metric_results.strategy_id', '=', 'lm.strategy_id')
                    ->on('strategy_metric_results.snapshot_at', '=', 'lm.max_at');
            })
            ->select('strategy_metric_results.*');
    }

    public function latestMetricValue(string $key): ?float
    {
        // Use latestMetrics cache when possible to reduce queries
        $metrics = $this->latestMetrics(null, [$key]);
        $metric = $metrics[$key] ?? null;

        return $metric?->value !== null ? (float) $metric->value : null;
    }

    /**
     * Получить последние рассчитанные метрики для стратегии на заданный момент времени.
     * По умолчанию берётся момент последнего снапшота (latest_snapshot_at, если подмешан
     * в запрос через selectSub, иначе — максимальный snapshot_at из strategy_metric_results).
     * Возвращает ассоциативный массив моделей StrategyMetricResult, индексированных по metric_key.
     * Можно ограничить список ключей метрик через $keys.
     * Результат кэшируется в пределах текущего запроса.
     *
     * @param  string[]|null  $keys
     * @return array<string, \App\Models\StrategyMetricResult>
     */
    public function latestMetrics(?\Carbon\CarbonInterface $at = null, ?array $keys = null): array
    {
        // Определяем момент времени
        if ($at === null) {
            if (! empty($this->latest_snapshot_at)) {
                try {
                    $at = Carbon::parse($this->latest_snapshot_at);
                } catch (\Throwable) {
                    $at = null;
                }
            }
            if ($at === null) {
                // Фоллбэк: берём максимальный snapshot_at из таблицы метрик
                $maxAt = $this->metrics()->max('snapshot_at');
                if ($maxAt) {
                    $at = Carbon::parse($maxAt);
                }
            }
        }

        if ($at === null) {
            return [];
        }

        $cacheKey = $at->toIso8601String();
        if (! isset($this->latestMetricsCache[$cacheKey])) {
            $query = $this->metrics()->where('snapshot_at', $at);
            $this->latestMetricsCache[$cacheKey] = $query
                ->get()
                ->keyBy('metric_key')
                ->all();
        }

        if ($keys === null) {
            return $this->latestMetricsCache[$cacheKey];
        }

        // Фильтрация по ключам
        $result = [];
        foreach ($keys as $k) {
            if (isset($this->latestMetricsCache[$cacheKey][$k])) {
                $result[$k] = $this->latestMetricsCache[$cacheKey][$k];
            }
        }

        return $result;
    }

    /**
     * Универсальный доступ к предзагруженным latestMetricsRows: карта metric_key => StrategyMetricResult.
     * Если отношение не предзагружено, выполняет безопасный запрос.
     * Позволяет в UI быстро получать конкретные метрики без повторных коллекций.
     *
     * @return array<string, \App\Models\StrategyMetricResult>
     */
    public function latestMetricsMapFromRows(): array
    {
        $rows = $this->relationLoaded('latestMetricsRows') ? $this->getRelation('latestMetricsRows') : $this->latestMetricsRows()->get();

        return collect($rows)->keyBy('metric_key')->all();
    }

    /**
     * Получить модель метрики из предзагруженных latestMetricsRows по ключу.
     */
    public function latestMetricFromRows(string $key): ?StrategyMetricResult
    {
        $map = $this->latestMetricsMapFromRows();

        return $map[$key] ?? null;
    }

    /**
     * Получить числовое значение метрики из предзагруженных latestMetricsRows по ключу.
     */
    public function latestMetricValueFromRows(string $key): ?float
    {
        $row = $this->latestMetricFromRows($key);

        return $row && $row->value !== null ? (float) $row->value : null;
    }

    /**
     * Получает последний снимок стратегии.
     *
     * @return Snapshot|null Возвращает последний снимок или null, если снимков нет
     */
    public function getLatestSnapshot(): ?Snapshot
    {
        if (! $this->hasSnapshots()) {
            return null;
        }

        return $this->snapshots()->latest()->first();
    }

    /**
     * Проверяет, есть ли у стратегии снимки.
     */
    public function hasSnapshots(): bool
    {
        return $this->snapshots()->exists();
    }

    /**
     * Возвращает общую стоимость стратегии на указанную дату.
     * Включает ликвидность, неполученные комиссии из последнего снимка до указанной даты
     * и сумму всех собранных комиссий до указанной даты.
     *
     * @param  Carbon|null  $date  Дата, на которую нужно получить стоимость. Если null, используется текущая дата
     */
    public function getTotalValue(?Carbon $date = null): float
    {
        $snapshotValue = 0;

        if ($date) {
            $snapshot = $this->snapshots()
                ->where('created_at', '<=', $date)
                ->latest()
                ->first();
        } else {
            $snapshot = $this->getLatestSnapshot();
        }
        //        dump($snapshot);
        if ($snapshot) {
            $snapshotValue = $snapshot->total_liquidity + $snapshot->fees_uncollected;
        }

        //        $collectedFees = $this->getTransactionsAmountAtDate(TransactionType::COLLECT_FEES, $date);

        //        return $snapshotValue + $collectedFees;
        return $snapshotValue;
    }
}
