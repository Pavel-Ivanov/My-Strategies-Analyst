<?php

declare(strict_types=1);

use App\Models\Chain;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('allows owner to view private media and forbids others', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $other = User::factory()->create();

    $chain = Chain::query()->create([
        'user_id' => $owner->getKey(),
        'name' => 'Test',
    ]);

    // Attach a fake image to the media collection
    $file = UploadedFile::fake()->image('icon.jpg', 100, 100);
    $chain->addMedia($file->getPathname())
        ->usingFileName('icon.jpg')
        ->toMediaCollection('chain-icons');

    /** @var Media $media */
    $media = $chain->getFirstMedia('chain-icons');
    expect($media)->not->toBeNull();

    // Owner can access
    $this->actingAs($owner);
    $this->get(route('media.show', ['media' => $media->getKey()]))
        ->assertSuccessful();

    // Other user is forbidden
    $this->actingAs($other);
    $this->get(route('media.show', ['media' => $media->getKey()]))
        ->assertForbidden();
});
