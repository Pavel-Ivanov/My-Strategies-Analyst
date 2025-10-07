<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Тип стратегии
 */
enum StrategyType: string implements HasColor, HasIcon, HasLabel
{
    case STAKING = 'staking';
    case LENDING = 'lending';
    case BORROWING = 'borrowing';
    case FARMING = 'farming';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STAKING => 'Стейкинг',
            self::LENDING => 'Кредитование',
            self::BORROWING => 'Заимствование',
            self::FARMING => 'Фарминг',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::STAKING => 'heroicon-o-lock-closed',
            self::LENDING => 'heroicon-o-arrow-up-tray',
            self::BORROWING => 'heroicon-o-arrow-down-tray',
            self::FARMING => 'heroicon-o-banknotes',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::STAKING => 'primary',
            self::LENDING => 'info',
            self::BORROWING => 'warning',
            self::FARMING => 'success',
        };
    }
}
