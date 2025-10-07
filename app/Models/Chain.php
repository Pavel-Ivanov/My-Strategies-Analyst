<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Chain extends Model implements HasMedia
{
    use BelongsToUser, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
    ];

    protected static function booted(): void
    {
        // Автоматически удаляем все медиа при удалении модели
        static::deleting(function (Chain $chain) {
            $chain->clearMediaCollection('chain-icons');
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('chain-icons')
            ->useDisk('local')
            ->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
