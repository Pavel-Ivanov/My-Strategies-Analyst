<?php

declare(strict_types=1);

use App\Filament\Resources\Chains\Pages\CreateChain;
use App\Filament\Resources\Chains\Pages\EditChain;
use App\Filament\Resources\Chains\Pages\ListChains;
use App\Models\Chain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe('Chain Resource - List Page', function (): void {
    it('can load the list page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListChains::class)
            ->assertOk();
    });

    it('can display chains in the table', function (): void {
        $user = User::factory()->create();

        $chains = collect([
            Chain::query()->create([
                'user_id' => $user->id,
                'name' => 'Ethereum',
            ]),
            Chain::query()->create([
                'user_id' => $user->id,
                'name' => 'Polygon',
            ]),
        ]);

        $this->actingAs($user);

        Livewire::test(ListChains::class)
            ->assertOk()
            ->assertCanSeeTableRecords($chains);
    });

    it('can only see own chains', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $userChain = Chain::query()->create([
            'user_id' => $user1->id,
            'name' => 'Ethereum',
        ]);

        $otherUserChain = Chain::query()->create([
            'user_id' => $user2->id,
            'name' => 'Polygon',
        ]);

        $this->actingAs($user1);

        Livewire::test(ListChains::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$userChain])
            ->assertCanNotSeeTableRecords([$otherUserChain]);
    });
});

describe('Chain Resource - Create Page', function (): void {
    it('can load the create page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateChain::class)
            ->assertOk();
    });

    it('can create a chain with required fields', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateChain::class)
            ->fillForm([
                'name' => 'Ethereum',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Chain::class, [
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);
    });

    it('validates required fields', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateChain::class)
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

        Livewire::test(CreateChain::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('Chain Resource - Edit Page', function (): void {
    it('can load the edit page', function (): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);

        $this->actingAs($user);

        Livewire::test(EditChain::class, [
            'record' => $chain->id,
        ])
            ->assertOk()
            ->assertFormSet([
                'name' => 'Ethereum',
            ]);
    });

    it('can update a chain', function (): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);

        $this->actingAs($user);

        Livewire::test(EditChain::class, [
            'record' => $chain->id,
        ])
            ->fillForm([
                'name' => 'Ethereum Mainnet',
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Chain::class, [
            'id' => $chain->id,
            'name' => 'Ethereum Mainnet',
        ]);
    });

    it('validates required fields on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);

        $this->actingAs($user);

        Livewire::test(EditChain::class, [
            'record' => $chain->id,
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
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);

        $this->actingAs($user);

        Livewire::test(EditChain::class, [
            'record' => $chain->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);

});

describe('Chain Resource - Delete', function (): void {
    it('can delete a chain from edit page', function (): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum',
        ]);

        $this->actingAs($user);

        Livewire::test(EditChain::class, [
            'record' => $chain->id,
        ])
            ->callAction('delete');

        expect(Chain::query()->where('id', $chain->id)->exists())->toBeFalse();
    });
});
