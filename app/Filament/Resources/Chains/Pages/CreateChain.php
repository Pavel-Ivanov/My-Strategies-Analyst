<?php

namespace App\Filament\Resources\Chains\Pages;

use App\Filament\Resources\Chains\ChainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChain extends CreateRecord
{
    protected static string $resource = ChainResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }
}
