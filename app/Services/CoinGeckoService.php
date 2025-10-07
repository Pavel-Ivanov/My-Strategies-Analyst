<?php

namespace App\Services;

use App\Models\Asset;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class CoinGeckoService
{
    /**
     * Fetch price for an asset from CoinGecko
     *
     * @param  Asset  $asset  The asset to fetch price for
     * @param  bool  $showNotifications  Whether to show notifications
     * @return float|null The price in USD or null if not found
     */
    public function fetchPrice(Asset $asset, bool $showNotifications = true): ?float
    {
        try {
            // First try using coingecko_asset_id if available
            if (! empty($asset->coingecko_asset_id)) {
                $price = $this->fetchPriceById($asset->coingecko_asset_id);

                if ($price !== null) {
                    if ($showNotifications) {
                        $this->sendNotification(
                            'Price Updated',
                            "Successfully fetched price for {$asset->symbol}: \${$price}",
                            'success'
                        );
                    }

                    return $price;
                }
            }

            // Fallback to searching by symbol if coingecko_asset_id is not available or failed
            return $this->fetchPriceBySymbol($asset->symbol, $showNotifications);
        } catch (Exception $e) {
            if ($showNotifications) {
                $this->sendNotification(
                    'Error',
                    $e->getMessage(),
                    'danger'
                );
            }

            return null;
        }
    }

    /**
     * Fetch price for an asset by its CoinGecko ID
     *
     * @param  string  $coinId  The CoinGecko ID of the asset
     * @return float|null The price in USD or null if not found
     */
    public function fetchPriceById(string $coinId): ?float
    {
        $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => $coinId,
            'vs_currencies' => 'usd',
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return $data[$coinId]['usd'] ?? null;
        }

        return null;
    }

    /**
     * Fetch price for an asset by its symbol
     *
     * @param  string  $symbol  The symbol of the asset
     * @param  bool  $showNotifications  Whether to show notifications
     * @return float|null The price in USD or null if not found
     */
    public function fetchPriceBySymbol(string $symbol, bool $showNotifications = true): ?float
    {
        $response = Http::get('https://api.coingecko.com/api/v3/search', [
            'query' => $symbol,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $coins = $data['coins'] ?? [];

            if (! empty($coins)) {
                // Get the first result (most relevant)
                $coinId = $coins[0]['id'] ?? null;

                if ($coinId) {
                    $price = $this->fetchPriceById($coinId);

                    if ($price !== null) {
                        if ($showNotifications) {
                            $this->sendNotification(
                                'Price Updated',
                                "Successfully fetched price for {$symbol}: \${$price}",
                                'success'
                            );
                        }

                        return $price;
                    }
                }
            }

            if ($showNotifications) {
                $this->sendNotification(
                    'Price Not Found',
                    "Could not find price for {$symbol} on CoinGecko",
                    'warning'
                );
            }
        } else {
            if ($showNotifications) {
                $this->sendNotification(
                    'API Error',
                    "Failed to search for {$symbol} on CoinGecko",
                    'danger'
                );
            }
        }

        return null;
    }

    /**
     * Send a notification
     *
     * @param  string  $title  The notification title
     * @param  string  $body  The notification body
     * @param  string  $type  The notification type (success, warning, danger)
     */
    private function sendNotification(string $title, string $body, string $type = 'success'): void
    {
        $notification = Notification::make()
            ->title($title)
            ->body($body);

        switch ($type) {
            case 'success':
                $notification->success();
                break;
            case 'warning':
                $notification->warning();
                break;
            case 'danger':
                $notification->danger();
                break;
        }

        $notification->send();
    }
}
