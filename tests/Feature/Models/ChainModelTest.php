<?php

declare(strict_types=1);

use App\Models\Chain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a chain via Eloquent and links to user', function (): void {
    $user = User::factory()->create();

    $chain = Chain::query()->create([
        'user_id' => $user->id,
        'name' => 'Ethereum',
    ]);

    expect($chain->exists)->toBeTrue();
    expect($chain->user)->not->toBeNull();
    expect($chain->user->is($user))->toBeTrue();
});

it('auto-sets user_id on create when authenticated and applies global scope', function (): void {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    // Acting as first user, create without specifying user_id
    $this->actingAs($u1);
    $c1 = Chain::query()->create([
        'name' => 'Mainnet',
    ]);

    expect($c1->user_id)->toBe($u1->id);

    // Create a record for second user explicitly (out of current auth)
    $c2 = Chain::query()->create([
        'user_id' => $u2->id,
        'name' => 'Sidechain',
    ]);

    // Due to global scope, querying while authenticated as u1 should not show u2's record
    $allForU1 = Chain::query()->pluck('id')->all();

    expect($allForU1)->toContain($c1->id);
    expect($allForU1)->not->toContain($c2->id);
});
