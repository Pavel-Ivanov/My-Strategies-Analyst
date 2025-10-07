<?php

namespace App\Filament\Resources\Chains;

use App\Filament\Resources\Chains\Pages\CreateChain;
use App\Filament\Resources\Chains\Pages\EditChain;
use App\Filament\Resources\Chains\Pages\ListChains;
use App\Filament\Resources\Chains\Schemas\ChainForm;
use App\Filament\Resources\Chains\Tables\ChainsTable;
use App\Models\Chain;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChainResource extends Resource
{
    protected static ?string $model = Chain::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'Chain';

    public static function form(Schema $schema): Schema
    {
        return ChainForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChainsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChains::route('/'),
            'create' => CreateChain::route('/create'),
            'edit' => EditChain::route('/{record}/edit'),
        ];
    }
}
