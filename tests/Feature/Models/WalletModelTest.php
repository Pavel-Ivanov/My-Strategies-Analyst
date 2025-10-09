<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates a wallet via Eloquent and links to a user', function (): void {
    $user = User::factory()->create();

    $wallet = Wallet::query()->create([
        'user_id' => $user->id,
        'name' => 'Main Wallet',
    ]);

    expect($wallet->exists)->toBeTrue();
    expect($wallet->name)->toBe('Main Wallet');
    expect($wallet->user_id)->toBe($user->id);
});

it('auto-assigns user_id on create when authenticated', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $wallet = Wallet::query()->create([
        'name' => 'Trading Wallet',
    ]);

    expect($wallet->user_id)->toBe($user->id);
});

it('applies a global scope to only return records for the authenticated user', function (): void {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    // Authenticate as user 1
    $this->actingAs($u1);

    // Create wallet via Eloquent (will be assigned to u1)
    Wallet::query()->create([
        'name' => 'My Wallet',
    ]);

    // Insert another user's wallet directly via DB to bypass the creating hook
    DB::table('wallets')->insert([
        'user_id' => $u2->id,
        'name' => 'Other Wallet',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Queries using models should only see u1's records due to the global scope
    expect(Wallet::query()->count())->toBe(1);

    // Additionally ensure returned row belongs to u1
    expect(Wallet::query()->first()->user_id)->toBe($u1->id);
});

it('has a belongsTo relationship with User', function (): void {
    $user = User::factory()->create();

    $wallet = Wallet::query()->create([
        'user_id' => $user->id,
        'name' => 'Test Wallet',
    ]);

    expect($wallet->user)->toBeInstanceOf(User::class);
    expect($wallet->user->id)->toBe($user->id);
});
