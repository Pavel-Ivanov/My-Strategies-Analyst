<?php

declare(strict_types=1);

use App\Models\Asset;
use App\Models\Chain;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('deletes user with all related data and media files without errors', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();

    // Create related models with media
    $chain = Chain::factory()->create([
        'user_id' => $user->id,
    ]);

    $asset = Asset::factory()->create([
        'user_id' => $user->id,
    ]);

    $resource = Resource::factory()->create([
        'user_id' => $user->id,
    ]);

    // Add media to chain
    $file = UploadedFile::fake()->image('icon.jpg', 100, 100);
    $chain->addMedia($file->getPathname())
        ->usingFileName('icon.jpg')
        ->toMediaCollection('chain-icons');

    // Verify files exist
    Storage::disk('local')->assertExists("users/{$user->id}/chain-icons/{$chain->getFirstMedia('chain-icons')->id}/icon.jpg");

    // Delete user
    $user->delete();

    // Verify user is deleted
    expect(User::query()->find($user->id))->toBeNull();

    // Verify related models are cascade deleted
    expect(Chain::query()->find($chain->id))->toBeNull();
    expect(Asset::query()->find($asset->id))->toBeNull();
    expect(Resource::query()->find($resource->id))->toBeNull();

    // Verify all user files are deleted
    Storage::disk('local')->assertDirectoryEmpty('users');
});

it('handles user deletion when user has no related data', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();

    // Delete user without any related data
    $user->delete();

    // Verify user is deleted
    expect(User::query()->find($user->id))->toBeNull();
});

it('handles chain deletion independently without affecting user', function (): void {
    Storage::fake('local');

    $user = User::factory()->create();

    $chain = Chain::factory()->create([
        'user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->image('icon.jpg', 100, 100);
    $chain->addMedia($file->getPathname())
        ->usingFileName('icon.jpg')
        ->toMediaCollection('chain-icons');

    $mediaId = $chain->getFirstMedia('chain-icons')->id;

    // Verify file exists
    Storage::disk('local')->assertExists("users/{$user->id}/chain-icons/{$mediaId}/icon.jpg");

    // Delete only the chain
    $chain->delete();

    // Verify chain is deleted
    expect(Chain::query()->find($chain->id))->toBeNull();

    // Verify user still exists
    expect(User::query()->find($user->id))->not->toBeNull();

    // Verify chain media is deleted
    Storage::disk('local')->assertMissing("users/{$user->id}/chain-icons/{$mediaId}/icon.jpg");
});
