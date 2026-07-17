<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsClient
{
    public function getNews(string $category = 'logistics', ?string $countryCode = null): array
    {
        $apiKey = env('GNEWS_API_KEY');

        if (!$apiKey) {
            Log::info('GNEWS_API_KEY not set, skipping news fetch.');
            return [];
        }

        try {
            // Map category to search query terms
            $query = match ($category) {
                'shipping' => 'shipping logistics port cargo',
                'trade' => 'international trade exports imports tariff',
                'economy' => 'economic inflation gdp recession currency',
                default => 'supply chain logistics freight cargo shipping'
            };

            if ($countryCode) {
                $query .= " " . strtolower($countryCode);
            }

            $response = Http::timeout(5)
                ->retry(1, 100)
                ->get("https://gnews.io/api/v4/search", [
                    'q' => $query,
                    'lang' => 'en',
                    'apikey' => $apiKey,
                    'max' => 10
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $articles = $data['articles'] ?? [];

                $result = [];
                foreach ($articles as $art) {
                    $result[] = [
                        'title' => $art['title'] ?? '',
                        'description' => $art['description'] ?? '',
                        'url' => $art['url'] ?? '',
                        'image_url' => $art['image'] ?? null,
                        'published_at' => $art['publishedAt'] ?? now()->toIso8601String(),
                        'source' => $art['source']['name'] ?? 'GNews'
                    ];
                }
                return $result;
            }

            Log::warning("GNews API responded with status {$response->status()} for query '{$query}'.");
        } catch (\Exception $e) {
            Log::warning("GNews API error: " . $e->getMessage());
        }

        return [];
    }
}
