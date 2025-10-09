<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Chain extends Model implements HasMedia
{
    use BelongsToUser, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * Boot the model.
     * Automatically deletes all media when the model is deleted.
     */
    protected static function booted(): void
    {
        // Automatically delete all media when the model is deleted
        static::deleting(function (Chain $chain) {
            $chain->clearMediaCollection('chain-icons');
        });
    }

    /**
     * Register media collections for the chain.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('chain-icons')
            ->useDisk('local')
            ->singleFile();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Asset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
