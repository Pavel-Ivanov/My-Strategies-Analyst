<?php

declare(strict_types=1);

use App\Filament\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Resources\Wallets\Pages\EditWallet;
use App\Filament\Resources\Wallets\Pages\ListWallets;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe('Wallet Resource - List Page', function (): void {
    it('can load the list page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListWallets::class)
            ->assertOk();
    });

    it('can display wallets in the table', function (): void {
        $user = User::factory()->create();

        $wallets = collect([
            Wallet::factory()->create([
                'user_id' => $user->id,
                'name' => 'MetaMask',
            ]),
            Wallet::factory()->create([
                'user_id' => $user->id,
                'name' => 'Trust Wallet',
            ]),
        ]);

        $this->actingAs($user);

        Livewire::test(ListWallets::class)
            ->assertOk()
            ->assertCanSeeTableRecords($wallets);
    });

    it('can only see own wallets', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $userWallet = Wallet::factory()->create([
            'user_id' => $user1->id,
            'name' => 'MetaMask',
        ]);

        $otherUserWallet = Wallet::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Trust Wallet',
        ]);

        $this->actingAs($user1);

        Livewire::test(ListWallets::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$userWallet])
            ->assertCanNotSeeTableRecords([$otherUserWallet]);
    });
});

describe('Wallet Resource - Create Page', function (): void {
    it('can load the create page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateWallet::class)
            ->assertOk();
    });

    it('can create a wallet with required fields', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateWallet::class)
            ->fillForm([
                'name' => 'MetaMask',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Wallet::class, [
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);
    });

    it('validates required fields', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateWallet::class)
            ->fillForm([
                'name' => 'MetaMask',
                ...$data,
            ])
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

        Livewire::test(CreateWallet::class)
            ->fillForm([
                'name' => 'MetaMask',
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);

    it('allows same wallet name for different users on create', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Wallet::factory()->create([
            'user_id' => $user1->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user2);

        Livewire::test(CreateWallet::class)
            ->fillForm([
                'name' => 'MetaMask',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Wallet::class, [
            'user_id' => $user2->id,
            'name' => 'MetaMask',
        ]);
    });
});

describe('Wallet Resource - Edit Page', function (): void {
    it('can load the edit page', function (): void {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
        ])
            ->assertOk()
            ->assertFormSet([
                'name' => 'MetaMask',
            ]);
    });

    it('can update a wallet', function (): void {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
        ])
            ->fillForm([
                'name' => 'MetaMask Updated',
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Wallet::class, [
            'id' => $wallet->id,
            'name' => 'MetaMask Updated',
        ]);
    });

    it('validates required fields on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
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
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);

    it('allows same wallet name for different users on update', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Wallet::factory()->create([
            'user_id' => $user1->id,
            'name' => 'MetaMask',
        ]);

        $wallet = Wallet::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Trust Wallet',
        ]);

        $this->actingAs($user2);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
        ])
            ->fillForm([
                'name' => 'MetaMask',
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Wallet::class, [
            'id' => $wallet->id,
            'name' => 'MetaMask',
        ]);
    });
});

describe('Wallet Resource - Delete', function (): void {
    it('can delete a wallet from edit page', function (): void {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'MetaMask',
        ]);

        $this->actingAs($user);

        Livewire::test(EditWallet::class, [
            'record' => $wallet->id,
        ])
            ->callAction('delete');

        expect(Wallet::query()->where('id', $wallet->id)->exists())->toBeFalse();
    });
});
