<?php

namespace App\Filament\Resources\Resources\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResourceForm
{
    /**
     * Configure the resource form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('resource-icon')
                    ->collection('resource-icons'),
            ]);
    }
}
