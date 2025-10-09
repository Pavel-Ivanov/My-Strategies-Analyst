<?php

namespace App\Filament\Resources\Wallets;

use App\Filament\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Resources\Wallets\Pages\EditWallet;
use App\Filament\Resources\Wallets\Pages\ListWallets;
use App\Filament\Resources\Wallets\Schemas\WalletForm;
use App\Filament\Resources\Wallets\Tables\WalletsTable;
use App\Models\Wallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    /**
     * Configure the wallet form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return WalletForm::configure($schema);
    }

    /**
     * Configure the wallets table.
     */
    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }

    /**
     * Get the relationships for the resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages available for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListWallets::route('/'),
            'create' => CreateWallet::route('/create'),
            'edit' => EditWallet::route('/{record}/edit'),
        ];
    }
}
