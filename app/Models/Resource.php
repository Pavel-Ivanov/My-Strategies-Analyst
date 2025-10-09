<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Resource extends Model implements HasMedia
{
    use BelongsToUser, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
    ];

    protected static function booted(): void
    {
        // Automatically delete all media when the model is deleted
        static::deleting(function (Resource $resource) {
            $resource->clearMediaCollection('resource-icons');
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('resource-icons')
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
     * @return HasMany<Strategy, $this>
     */
    public function strategies(): HasMany
    {
        return $this->hasMany(Strategy::class);
    }
}
