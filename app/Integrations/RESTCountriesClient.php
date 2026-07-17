<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RESTCountriesClient
{
    public function getCountry(string $iso2): ?array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get("https://restcountries.com/v3.1/alpha/{$iso2}");

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && is_array($data)) {
                    $item = $data[0];
                    
                    // Extract currency code, name, and symbol
                    $currencies = $item['currencies'] ?? [];
                    $currencyCode = array_key_first($currencies);
                    $currencyName = $currencies[$currencyCode]['name'] ?? '';
                    $currencySymbol = $currencies[$currencyCode]['symbol'] ?? '';

                    return [
                        'iso2' => $iso2,
                        'name' => $item['name']['common'] ?? '',
                        'official_name' => $item['name']['official'] ?? '',
                        'capital' => $item['capital'][0] ?? '',
                        'region' => $item['region'] ?? '',
                        'languages' => $item['languages'] ?? [],
                        'flag_url' => $item['flags']['png'] ?? $item['flags']['svg'] ?? '',
                        'latitude' => $item['latlng'][0] ?? 0.0,
                        'longitude' => $item['latlng'][1] ?? 0.0,
                        'currency_code' => $currencyCode,
                        'currency_name' => $currencyName,
                        'currency_symbol' => $currencySymbol,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("RESTCountriesClient error for {$iso2}: " . $e->getMessage());
        }

        return null;
    }
}
