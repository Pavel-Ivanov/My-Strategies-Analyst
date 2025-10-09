<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AssetsTable
{
    /**
     * Configure the assets table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('asset_type')
                    ->label('')
                    ->tooltip(fn ($record) => $record->asset_type->getLabel()),
                SpatieMediaLibraryImageColumn::make('asset-icon')
                    ->label('')
                    ->collection('asset-icons')
                    ->imageSize(30),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chain.name')
                    ->default('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('asset_contract_address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coingecko_asset_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_updatable'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel(),
            ]);
    }
}
