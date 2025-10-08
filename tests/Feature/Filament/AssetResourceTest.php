<?php

declare(strict_types=1);

use App\Enums\AssetType;
use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\EditAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Models\Asset;
use App\Models\Chain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe('Asset Resource - List Page', function (): void {
    it('can load the list page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListAssets::class)
            ->assertOk();
    });

    it('can display assets in the table', function (): void {
        $user = User::factory()->create();

        $assets = collect([
            Asset::query()->create([
                'user_id' => $user->id,
                'asset_type' => 'coin',
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'is_updatable' => true,
            ]),
            Asset::query()->create([
                'user_id' => $user->id,
                'asset_type' => 'stablecoin',
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'is_updatable' => false,
            ]),
        ]);

        $this->actingAs($user);

        Livewire::test(ListAssets::class)
            ->assertOk()
            ->assertCanSeeTableRecords($assets);
    });

    it('can only see own assets', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $userAsset = Asset::query()->create([
            'user_id' => $user1->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $otherUserAsset = Asset::query()->create([
            'user_id' => $user2->id,
            'asset_type' => 'stablecoin',
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'is_updatable' => false,
        ]);

        $this->actingAs($user1);

        Livewire::test(ListAssets::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$userAsset])
            ->assertCanNotSeeTableRecords([$otherUserAsset]);
    });
});

describe('Asset Resource - Create Page', function (): void {
    it('can load the create page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateAsset::class)
            ->assertOk();
    });

    it('can create an asset with required fields', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateAsset::class)
            ->fillForm([
                'asset_type' => 'coin',
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'is_updatable' => true,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Asset::class, [
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);
    });

    it('can create an asset with all fields', function (): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum Mainnet',
            'slug' => 'ethereum',
            'is_testnet' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateAsset::class)
            ->fillForm([
                'asset_type' => 'stablecoin',
                'name' => 'Tether',
                'symbol' => 'USDT',
                'chain_id' => $chain->id,
                'asset_contract_address' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
                'coingecko_asset_id' => 'tether',
                'is_updatable' => false,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Asset::class, [
            'user_id' => $user->id,
            'asset_type' => 'stablecoin',
            'name' => 'Tether',
            'symbol' => 'USDT',
            'chain_id' => $chain->id,
            'asset_contract_address' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
            'coingecko_asset_id' => 'tether',
            'is_updatable' => false,
        ]);
    });

    it('validates required fields', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateAsset::class)
            ->fillForm([
                'asset_type' => 'coin',
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'is_updatable' => true,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'asset_type is required' => [['asset_type' => null], ['asset_type' => 'required']],
        'name is required' => [['name' => null], ['name' => 'required']],
        'symbol is required' => [['symbol' => null], ['symbol' => 'required']],
        'is_updatable is required' => [['is_updatable' => null], ['is_updatable' => 'required']],
    ]);

    it('validates max length constraints', function (array $data, array $errors): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateAsset::class)
            ->fillForm([
                'asset_type' => 'coin',
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'is_updatable' => true,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified()
            ->assertNoRedirect();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        'symbol max 255 characters' => [['symbol' => Str::random(256)], ['symbol' => 'max']],
        'asset_contract_address max 255 characters' => [['asset_contract_address' => Str::random(256)], ['asset_contract_address' => 'max']],
        'coingecko_asset_id max 255 characters' => [['coingecko_asset_id' => Str::random(256)], ['coingecko_asset_id' => 'max']],
    ]);
});

describe('Asset Resource - View Page', function (): void {
    it('can load the view page', function (): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ViewAsset::class, [
            'record' => $asset->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'asset_type' => AssetType::COIN,
                'is_updatable' => true,
            ]);
    });
});

describe('Asset Resource - Edit Page', function (): void {
    it('can load the edit page', function (): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->assertOk()
            ->assertFormSet([
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'asset_type' => AssetType::COIN,
                'is_updatable' => true,
            ]);
    });

    it('can update an asset', function (): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->fillForm([
                'name' => 'Bitcoin Updated',
                'symbol' => 'BTC-NEW',
                'asset_type' => 'stablecoin',
                'is_updatable' => false,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Asset::class, [
            'id' => $asset->id,
            'name' => 'Bitcoin Updated',
            'symbol' => 'BTC-NEW',
            'asset_type' => 'stablecoin',
            'is_updatable' => false,
        ]);
    });

    it('can update asset with chain and contract address', function (): void {
        $user = User::factory()->create();
        $chain = Chain::query()->create([
            'user_id' => $user->id,
            'name' => 'Ethereum Mainnet',
            'slug' => 'ethereum',
            'is_testnet' => false,
        ]);

        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->fillForm([
                'chain_id' => $chain->id,
                'asset_contract_address' => '0x1234567890abcdef',
                'coingecko_asset_id' => 'bitcoin',
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Asset::class, [
            'id' => $asset->id,
            'chain_id' => $chain->id,
            'asset_contract_address' => '0x1234567890abcdef',
            'coingecko_asset_id' => 'bitcoin',
        ]);
    });

    it('validates required fields on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'asset_type is required' => [['asset_type' => null], ['asset_type' => 'required']],
        'name is required' => [['name' => null], ['name' => 'required']],
        'symbol is required' => [['symbol' => null], ['symbol' => 'required']],
        'is_updatable is required' => [['is_updatable' => null], ['is_updatable' => 'required']],
    ]);

    it('validates max length constraints on update', function (array $data, array $errors): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        'symbol max 255 characters' => [['symbol' => Str::random(256)], ['symbol' => 'max']],
        'asset_contract_address max 255 characters' => [['asset_contract_address' => Str::random(256)], ['asset_contract_address' => 'max']],
        'coingecko_asset_id max 255 characters' => [['coingecko_asset_id' => Str::random(256)], ['coingecko_asset_id' => 'max']],
    ]);

});

describe('Asset Resource - Delete', function (): void {
    it('can delete an asset from edit page', function (): void {
        $user = User::factory()->create();
        $asset = Asset::query()->create([
            'user_id' => $user->id,
            'asset_type' => 'coin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_updatable' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsset::class, [
            'record' => $asset->id,
        ])
            ->callAction('delete');

        expect(Asset::query()->where('id', $asset->id)->exists())->toBeFalse();
    });
});
