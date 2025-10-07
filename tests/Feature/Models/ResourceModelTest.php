<?php

declare(strict_types=1);

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a resource via Eloquent and links to a user', function (): void {
    $user = User::factory()->create();

    $resource = Resource::query()->create([
        'user_id' => $user->id,
        'name' => 'Docs',
    ]);

    expect($resource->exists)->toBeTrue();
    expect($resource->name)->toBe('Docs');
});
