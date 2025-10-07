<?php

namespace App\Models;

use App\Enums\AssetType;
use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Asset extends Model implements HasMedia
{
    use BelongsToUser, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'asset_type',
        'name',
        'symbol',
        'asset_contract_address',
        'coingecko_asset_id',
        'is_updatable',
        'icon_url',
    ];

    protected $casts = [
        'asset_type' => AssetType::class,
        'is_updatable' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Automatically delete all media when the model is deleted
        static::deleting(function (Asset $asset) {
            $asset->clearMediaCollection();
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('asset-icons')
            ->useDisk('local')
            ->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo(Chain::class);
    }

    public function strategies(): BelongsToMany
    {
        return $this->belongsToMany(Strategy::class, 'asset_strategy', 'asset_id', 'strategy_id');
    }

    public function snapshots(): BelongsToMany
    {
        return $this->belongsToMany(Snapshot::class, 'asset_snapshot', 'asset_id', 'snapshot_id');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'asset_transaction', 'asset_id', 'transaction_id');
    }
}
