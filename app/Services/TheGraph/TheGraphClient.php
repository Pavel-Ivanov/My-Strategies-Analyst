<?php

namespace App\Services\TheGraph;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class TheGraphClient
{
    /** @var array<string,string> */
    private array $endpoints;

    public function __construct()
    {
        $this->endpoints = config('thegraph.endpoints', []);
    }

    /**
     * Fetch current pool state by chain and pool address from The Graph (Uniswap v3 subgraph).
     *
     * @param  string  $chainKey  One of: ethereum, polygon, arbitrum, optimism
     * @param  string  $poolAddress  Pool address (hex), case-insensitive
     * @return array<string,mixed>
     */
    public function getPoolState(string $chainKey, string $poolAddress): array
    {
        $chainKey = Str::of($chainKey)->lower()->toString();
        $endpoint = $this->endpoints[$chainKey] ?? null;
        if (! $endpoint) {
            throw new RuntimeException("No TheGraph endpoint configured for chain: {$chainKey}");
        }

        $query = <<<'GQL'
        query ($id: ID!) {
          pool(id: $id) {
            id
            liquidity
            sqrtPrice
            tick
            feeTier
            totalValueLockedUSD
            volumeUSD
            feesUSD
            token0 { id symbol decimals }
            token1 { id symbol decimals }
          }
        }
        GQL;

        $variables = [
            'id' => Str::lower($poolAddress),
        ];

        $response = Http::retry(2, 250)
            ->asJson()
            ->post($endpoint, [
                'query' => $query,
                'variables' => $variables,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('TheGraph request failed: '.$response->body());
        }

        $errors = $response->json('errors');
        if ($errors) {
            throw new RuntimeException('TheGraph returned errors: '.json_encode($errors));
        }

        $data = $response->json('data.pool');
        if (! $data) {
            throw new RuntimeException('Pool not found in The Graph response.');
        }

        return $data;
    }
}
