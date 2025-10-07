<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;

enum AssetType: string implements HasIcon
{
    case COIN = 'coin';
    case STABLECOIN = 'stablecoin';
    case BRIDGET_TOKEN = 'bridget token';
    case WRAPPED_TOKEN = 'wrapped token';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::COIN => 'Coin',
            self::STABLECOIN => 'Stablecoin',
            self::BRIDGET_TOKEN => 'Bridget token',
            self::WRAPPED_TOKEN => 'Wrapped token',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COIN => 'heroicon-o-currency-dollar',
            self::STABLECOIN => 'heroicon-o-banknotes',
            self::BRIDGET_TOKEN => 'heroicon-o-arrow-path-rounded-square',
            self::WRAPPED_TOKEN => 'heroicon-o-gift',
        };
    }
}
