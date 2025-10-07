<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StrategyStatus: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';
    case DRAFT = 'draft';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Активна',
            self::CLOSED => 'Закрыта',
            self::DRAFT => 'Черновик',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-m-check',
            self::CLOSED => 'heroicon-m-x-mark',
            self::DRAFT => 'heroicon-m-pencil',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::CLOSED => 'danger',
            self::DRAFT => 'gray',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
