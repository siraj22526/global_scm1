<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateClient
{
    public function getLatestRates(): array
    {
        $apiKey = env('EXCHANGERATE_API_KEY');
        $url = "https://open.er-api.com/v6/latest/USD";

        if ($apiKey) {
            $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD";
        }

        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // standard v6 and open.er-api both contain 'rates' or 'conversion_rates'
                $rates = $data['rates'] ?? $data['conversion_rates'] ?? [];
                
                return $rates;
            }
        } catch (\Exception $e) {
            Log::error("ExchangeRateClient error fetching rates: " . $e->getMessage());
        }

        return [];
    }
}
