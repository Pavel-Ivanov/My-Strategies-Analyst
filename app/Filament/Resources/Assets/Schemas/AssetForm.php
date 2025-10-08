<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Enums\AssetType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('General')
                    ->schema([
                        Select::make('asset_type')
                            ->options(AssetType::class)
                            ->default('coin')
                            ->required(),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('symbol')
                            ->label('Symbol')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Fieldset::make('Chain')
                    ->schema([
                        Select::make('chain_id')
                            ->relationship('chain', 'name'),
                        TextInput::make('asset_contract_address')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TextInput::make('coingecko_asset_id')
                    ->label('CoinGecko Asset ID')
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('asset-icon')
                    ->collection('asset-icons'),
                Toggle::make('is_updatable')
                    ->required(),
            ]);
    }
}
