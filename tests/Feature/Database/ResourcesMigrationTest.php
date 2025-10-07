<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('can insert a resource with a name for a user', function (): void {
    $user = User::factory()->create();

    $id = DB::table('resources')->insertGetId([
        'user_id' => $user->id,
        'name' => 'Core Docs',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($id)->toBeInt()->toBeGreaterThan(0);

    $record = DB::table('resources')->find($id);

    expect($record)
        ->not()->toBeNull()
        ->and($record->name)->toBe('Core Docs')
        ->and($record->user_id)->toBe($user->id);
});

it('enforces unique name per user but allows same name across users', function (): void {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $name = 'Docs';

    DB::table('resources')->insert([
        'user_id' => $u1->id,
        'name' => $name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Same name for a different user should be allowed
    DB::table('resources')->insert([
        'user_id' => $u2->id,
        'name' => $name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Duplicate for the same user should fail due to unique(user_id, name)
    $this->expectException(\Illuminate\Database\QueryException::class);

    DB::table('resources')->insert([
        'user_id' => $u1->id,
        'name' => $name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});
