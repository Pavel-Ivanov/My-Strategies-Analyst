<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        // 1. При создании записи автоматически присвоить ID текущего пользователя
        static::creating(function (Model $model): void {
            if (Auth::check() && ! $model->user_id) {
                $model->user_id = Auth::id();
            }
        });

        // 2. Глобальная область (Scope): автоматически фильтровать все запросы
        static::addGlobalScope('user_id', function (Builder $builder): void {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }
}
