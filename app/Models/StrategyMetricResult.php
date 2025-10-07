<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyMetricResult extends Model
{
    protected $table = 'strategy_metric_results';

    protected $fillable = [
        'strategy_id',
        'metric_key',
        'value',
        'unit',
        'snapshot_at',
        'meta',
    ];

    protected $casts = [
        'snapshot_at' => 'datetime',
        'meta' => 'array',
        'value' => 'decimal:12',
    ];

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }
}
