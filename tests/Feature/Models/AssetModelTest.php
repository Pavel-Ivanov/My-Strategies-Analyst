<?php

declare(strict_types=1);

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates an asset via Eloquent and links to user', function (): void {
    $user = User::factory()->create();

    $asset = Asset::query()->create([
        'user_id' => $user->id,
        'asset_type' => 'coin',
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'is_updatable' => false,
    ]);

    expect($asset->exists)->toBeTrue();
    expect($asset->user)->not->toBeNull();
    expect($asset->user->is($user))->toBeTrue();
});
