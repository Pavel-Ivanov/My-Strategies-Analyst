<?php

namespace App\Filament\Resources\Chains\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('chain-icon')
                    ->collection('chain-icons'),
            ]);
    }
}
