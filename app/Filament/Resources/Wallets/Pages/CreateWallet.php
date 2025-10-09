<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\WalletResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWallet extends CreateRecord
{
    protected static string $resource = WalletResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }
}
