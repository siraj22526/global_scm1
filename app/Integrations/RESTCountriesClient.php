<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RESTCountriesClient
{
    /**
     * REST Countries deprecated its old anonymous restcountries.com/v3.1 JSON API
     * and now requires a free registered Bearer key on api.restcountries.com.
     * Without a key, calls are skipped and the country keeps its seeded/cached profile.
     */
    public function getCountry(string $iso2): ?array
    {
        $apiKey = env('REST_COUNTRIES_API_KEY');

        if (!$apiKey) {
            Log::info('REST_COUNTRIES_API_KEY not set, skipping country profile refresh.');
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->get("https://api.restcountries.com/v3.1/alpha/{$iso2}");

            if (!$response->successful()) {
                Log::warning("RESTCountriesClient responded with status {$response->status()} for {$iso2}.");
                return null;
            }

            $data = $response->json();

            // API wraps single-country lookups in a one-item array; reject anything else.
            $item = is_array($data) && isset($data[0]) && is_array($data[0]) ? $data[0] : null;
            if (!$item) {
                Log::warning("RESTCountriesClient returned no usable data for {$iso2}.");
                return null;
            }

            $currencies = $item['currencies'] ?? [];
            $currencyCode = array_key_first($currencies);
            $currencyName = $currencyCode ? ($currencies[$currencyCode]['name'] ?? '') : '';
            $currencySymbol = $currencyCode ? ($currencies[$currencyCode]['symbol'] ?? '') : '';

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
        } catch (\Exception $e) {
            Log::warning("RESTCountriesClient error for {$iso2}: " . $e->getMessage());
        }

        return null;
    }
}
