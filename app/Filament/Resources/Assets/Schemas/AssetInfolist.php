<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('asset_type'),
                TextEntry::make('name'),
                TextEntry::make('symbol'),
                TextEntry::make('chain_id')
                    ->numeric(),
                TextEntry::make('asset_contract_address'),
                TextEntry::make('coingecko_asset_id'),
                IconEntry::make('is_updatable')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
