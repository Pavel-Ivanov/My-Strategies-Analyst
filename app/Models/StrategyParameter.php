<?php

namespace App\Models;

use App\Enums\StrategyParameterType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategyParameter extends Model
{
    protected $fillable = [
        'name',
        'key',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => StrategyParameterType::class,
    ];

    /**
     * The strategies that belong to the parameter.
     */
    public function strategies(): BelongsToMany
    {
        return $this->belongsToMany(Strategy::class, 'strategy_strategy_parameter')
            ->using(StrategyStrategyParameter::class)
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Get the strategy parameter relationships.
     */
    public function strategyRelations(): HasMany
    {
        return $this->hasMany(StrategyStrategyParameter::class);
    }
}
