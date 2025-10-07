<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows same coingecko_asset_id for different users, but enforces uniqueness within a user', function (): void {
    // Create two users
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    // Same coingecko ID value
    $cgId = 'bitcoin';

    // Insert for user 1
    DB::table('assets')->insert([
        'user_id' => $u1->id,
        'asset_type' => 'coin',
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'coingecko_asset_id' => $cgId,
        'is_updatable' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert for user 2 with the same coingecko ID - should succeed
    DB::table('assets')->insert([
        'user_id' => $u2->id,
        'asset_type' => 'coin',
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'coingecko_asset_id' => $cgId,
        'is_updatable' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Duplicate for user 1 should fail due to unique(user_id, coingecko_asset_id)
    $this->expectException(\Illuminate\Database\QueryException::class);

    DB::table('assets')->insert([
        'user_id' => $u1->id,
        'asset_type' => 'coin',
        'name' => 'Bitcoin-2',
        'symbol' => 'BTC2',
        'coingecko_asset_id' => $cgId,
        'is_updatable' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});
