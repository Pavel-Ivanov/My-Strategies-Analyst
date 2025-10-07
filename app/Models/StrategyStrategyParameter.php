<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StrategyStrategyParameter extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'strategy_id',
        'strategy_parameter_id',
        'value',
    ];

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class, 'strategy_id', 'id');
    }

    public function strategyParameter(): BelongsTo
    {
        return $this->belongsTo(StrategyParameter::class, 'strategy_parameter_id', 'id');
    }
}
