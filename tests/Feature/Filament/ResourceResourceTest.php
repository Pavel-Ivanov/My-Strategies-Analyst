<?php

declare(strict_types=1);

use App\Filament\Resources\Resources\Pages\CreateResource;
use App\Filament\Resources\Resources\Pages\EditResource;
use App\Filament\Resources\Resources\Pages\ListResources;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe('Resource Resource - List Page', function (): void {
    it('can load the list page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListResources::class)
            ->assertOk();
    });

    it('can display resources in the table', function (): void {
        $user = User::factory()->create();

        $resources = collect([
            Resource::query()->create([
                'user_id' => $user->id,
                'name' => 'CPU Time',
            ]),
            Resource::query()->create([
                'user_id' => $user->id,
                'name' => 'Memory',
            ]),
        ]);

        $this->actingAs($user);

        Livewire::test(ListResources::class)
            ->assertOk()
            ->assertCanSeeTableRecords($resources);
    });

    it('can only see own resources', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $userResource = Resource::query()->create([
            'user_id' => $user1->id,
            'name' => 'CPU Time',
        ]);

        $otherUserResource = Resource::query()->create([
            'user_id' => $user2->id,
            'name' => 'Memory',
        ]);

        $this->actingAs($user1);

        Livewire::test(ListResources::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$userResource])
            ->assertCanNotSeeTableRecords([$otherUserResource]);
    });
});

describe('Resource Resource - Create Page', function (): void {
    it('can load the create page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateResource::class)
            ->assertOk();
    });

    it('can create a resource with required fields', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateResource::class)
            ->fillForm([
                'name' => 'CPU Time',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Resource::class, [
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);
    });

    it('validates required fields', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateResource::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'name is required' => [['name' => null], ['name' => 'required']],
    ]);

    it('validates max length constraints', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateResource::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('Resource Resource - Edit Page', function (): void {
    it('can load the edit page', function (): void {
        $user = User::factory()->create();
        $resource = Resource::query()->create([
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResource::class, [
            'record' => $resource->id,
        ])
            ->assertOk()
            ->assertFormSet([
                'name' => 'CPU Time',
            ]);
    });

    it('can update a resource', function (): void {
        $user = User::factory()->create();
        $resource = Resource::query()->create([
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResource::class, [
            'record' => $resource->id,
        ])
            ->fillForm([
                'name' => 'CPU Processing Time',
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Resource::class, [
            'id' => $resource->id,
            'name' => 'CPU Processing Time',
        ]);
    });

    it('validates required fields on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $resource = Resource::query()->create([
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResource::class, [
            'record' => $resource->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'name is required' => [['name' => null], ['name' => 'required']],
    ]);

    it('validates max length constraints on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $resource = Resource::query()->create([
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResource::class, [
            'record' => $resource->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);

});

describe('Resource Resource - Delete', function (): void {
    it('can delete a resource from edit page', function (): void {
        $user = User::factory()->create();
        $resource = Resource::query()->create([
            'user_id' => $user->id,
            'name' => 'CPU Time',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResource::class, [
            'record' => $resource->id,
        ])
            ->callAction('delete');

        expect(Resource::query()->where('id', $resource->id)->exists())->toBeFalse();
    });
});
