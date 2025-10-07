<?php

namespace App\Enums;

enum AssetType: string
{
    case COIN = 'coin';
    case STABLECOIN = 'stablecoin';
    case BRIDGET_TOKEN = 'bridget token';
    case WRAPPED_TOKEN = 'wrapped token';
}
