<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Wallet extends Model implements HasMedia
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
        static::deleting(function (Wallet $wallet) {
            $wallet->clearMediaCollection('wallet-icons');
        });
    }

    /**
     * Register media collections for the wallet.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('wallet-icons')
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
