<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StrategyParameterType: string implements HasColor, HasIcon, HasLabel
{
    case NUMERIC = 'numeric';
    case TEXT = 'text';
    case BOOLEAN = 'boolean';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NUMERIC => 'Числовой',
            self::TEXT => 'Текстовый',
            self::BOOLEAN => 'Логический',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NUMERIC => 'heroicon-o-calculator',
            self::TEXT => 'heroicon-o-document-text',
            self::BOOLEAN => 'heroicon-o-check-circle',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::NUMERIC => 'primary',
            self::TEXT => 'success',
            self::BOOLEAN => 'warning',
        };
    }
}
