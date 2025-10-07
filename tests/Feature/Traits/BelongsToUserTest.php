<?php

declare(strict_types=1);

use App\Models\Asset;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('auto-assigns user_id on create when authenticated (Asset, Resource)', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $asset = Asset::query()->create([
        'asset_type' => 'coin',
        'name' => 'Ethereum',
        'symbol' => 'ETH',
        'is_updatable' => false,
    ]);

    $resource = Resource::query()->create([
        'name' => 'Docs',
    ]);

    expect($asset->user_id)->toBe($user->id);
    expect($resource->user_id)->toBe($user->id);
});

it('applies a global scope to only return records for the authenticated user', function (): void {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    // Authenticate as user 1
    $this->actingAs($u1);

    // Create one Asset and one Resource via Eloquent (will be assigned to u1)
    Asset::query()->create([
        'asset_type' => 'coin',
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'is_updatable' => false,
    ]);

    Resource::query()->create([
        'name' => 'Guides',
    ]);

    // Insert another user's rows directly via DB to bypass the creating hook
    DB::table('assets')->insert([
        'user_id' => $u2->id,
        'asset_type' => 'coin',
        'name' => 'Solana',
        'symbol' => 'SOL',
        'is_updatable' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('resources')->insert([
        'user_id' => $u2->id,
        'name' => 'API',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Queries using models should only see u1's records due to the global scope
    expect(Asset::query()->count())->toBe(1);
    expect(Resource::query()->count())->toBe(1);

    // Additionally ensure returned rows belong to u1
    expect(Asset::query()->first()->user_id)->toBe($u1->id);
    expect(Resource::query()->first()->user_id)->toBe($u1->id);
});
