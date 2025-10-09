<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('can create a wallet using factory', function (): void {
    $wallet = Wallet::factory()->create();

    expect($wallet)->toBeInstanceOf(Wallet::class)
        ->and($wallet->name)->not->toBeEmpty()
        ->and($wallet->user_id)->not->toBeNull()
        ->and($wallet->user)->toBeInstanceOf(User::class);
});

it('can create a wallet for a specific user', function (): void {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    expect($wallet->user_id)->toBe($user->id)
        ->and($wallet->user->id)->toBe($user->id);
});

it('belongs to a user', function (): void {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    expect($wallet->user)->toBeInstanceOf(User::class)
        ->and($wallet->user->id)->toBe($user->id);
});

it('enforces unique wallet name per user', function (): void {
    $user = User::factory()->create();

    Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'My Wallet',
    ]);

    // This should throw a database exception due to unique constraint
    expect(fn () => Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'My Wallet',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('allows same wallet name for different users', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'My Wallet',
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'My Wallet',
    ]);

    expect($wallet1->name)->toBe('My Wallet')
        ->and($wallet2->name)->toBe('My Wallet')
        ->and($wallet1->user_id)->not->toBe($wallet2->user_id);
});

it('can add media to wallet-icons collection', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('wallet-icon.png', 100, 100);
    $wallet->addMedia($file->getPathname())
        ->usingFileName('wallet-icon.png')
        ->toMediaCollection('wallet-icons');

    expect($wallet->getFirstMedia('wallet-icons'))->not->toBeNull()
        ->and($wallet->getFirstMedia('wallet-icons')->file_name)->toBe('wallet-icon.png');

    Storage::disk('local')->assertExists("users/{$user->id}/wallet-icons/{$wallet->getFirstMedia('wallet-icons')->id}/wallet-icon.png");
});

it('allows only one media file in wallet-icons collection', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    // Add first media
    $file1 = UploadedFile::fake()->image('icon1.png', 100, 100);
    $wallet->addMedia($file1->getPathname())
        ->usingFileName('icon1.png')
        ->toMediaCollection('wallet-icons');

    $firstMediaId = $wallet->getFirstMedia('wallet-icons')->id;

    // Add second media (should replace first)
    $file2 = UploadedFile::fake()->image('icon2.png', 100, 100);
    $wallet->addMedia($file2->getPathname())
        ->usingFileName('icon2.png')
        ->toMediaCollection('wallet-icons');

    $wallet->refresh();

    expect($wallet->getMedia('wallet-icons')->count())->toBe(1)
        ->and($wallet->getFirstMedia('wallet-icons')->file_name)->toBe('icon2.png')
        ->and($wallet->getFirstMedia('wallet-icons')->id)->not->toBe($firstMediaId);
});

it('deletes wallet media when wallet is deleted', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('icon.png', 100, 100);
    $wallet->addMedia($file->getPathname())
        ->usingFileName('icon.png')
        ->toMediaCollection('wallet-icons');

    $mediaId = $wallet->getFirstMedia('wallet-icons')->id;

    // Verify file exists
    Storage::disk('local')->assertExists("users/{$user->id}/wallet-icons/{$mediaId}/icon.png");

    // Delete wallet
    $wallet->delete();

    // Verify wallet is deleted
    expect(Wallet::query()->find($wallet->id))->toBeNull();

    // Verify media is deleted
    Storage::disk('local')->assertMissing("users/{$user->id}/wallet-icons/{$mediaId}/icon.png");
});

it('cascades delete when user is deleted', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('icon.png', 100, 100);
    $wallet->addMedia($file->getPathname())
        ->usingFileName('icon.png')
        ->toMediaCollection('wallet-icons');

    // Delete user
    $user->delete();

    // Verify wallet is cascade deleted
    expect(Wallet::query()->find($wallet->id))->toBeNull();
});

it('can have multiple wallets per user', function (): void {
    $user = User::factory()->create();

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Wallet 1',
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Wallet 2',
    ]);

    expect(Wallet::query()->where('user_id', $user->id)->count())->toBe(2);
});

it('has timestamps', function (): void {
    $wallet = Wallet::factory()->create();

    expect($wallet->created_at)->not->toBeNull()
        ->and($wallet->updated_at)->not->toBeNull();
});
