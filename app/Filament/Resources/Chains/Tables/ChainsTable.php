<?php

namespace App\Filament\Resources\Chains\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChainsTable
{
    /**
     * Configure the chains table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('chain-icon')
                    ->label('')
                    ->collection('chain-icons')
                    ->imageSize(30),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel(),
            ])
/*            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])*/
            ->defaultSort('name', 'asc');
    }
}
