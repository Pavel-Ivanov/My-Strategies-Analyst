<?php

namespace App\Filament\Resources\Wallets\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WalletForm
{
    /**
     * Configure the wallet form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('wallet-icon')
                    ->collection('wallet-icons'),
            ]);
    }
}
