<?php

namespace Database\Factories;

use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'asset_type' => fake()->randomElement(AssetType::cases()),
            'name' => fake()->words(2, true),
            'symbol' => strtoupper(fake()->lexify('???')),
            'chain_id' => null,
            'asset_contract_address' => null,
            'coingecko_asset_id' => null,
            'is_updatable' => false,
        ];
    }
}
