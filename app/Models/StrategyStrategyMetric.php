<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StrategyStrategyMetric extends Pivot
{
    public $incrementing = true;

    //    protected $table = 'strategy_strategy_metric';

    protected $fillable = [
        'strategy_id',
        'metric_key',
        'is_enabled',
        'custom_config',
        'order',
    ];

    protected $casts = [
        'custom_config' => 'array',
        'is_enabled' => 'boolean',
        'order' => 'integer',
    ];

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class, 'strategy_id', 'id');
    }
}
