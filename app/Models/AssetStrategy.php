<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AssetStrategy extends Pivot
{
    public $incrementing = true;

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }
}
