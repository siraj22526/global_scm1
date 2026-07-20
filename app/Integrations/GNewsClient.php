<?php

namespace App\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsClient
{
    private const COOLDOWN_CACHE_KEY = 'gnews:rate_limited_until';

    public function getNews(string $category = 'logistics', ?string $countryName = null): array
    {
        $apiKey = config('services.gnews.key');

        if (!$apiKey) {
            Log::info('GNEWS_API_KEY not set, skipping news fetch.');
            return [];
        }

        if (Cache::has(self::COOLDOWN_CACHE_KEY)) {
            Log::info('GNews API is in cooldown after hitting rate limit; skipping fetch and serving cached data.');
            return [];
        }

        try {
            // GNews combines bare space-separated words with AND, not OR, so plain
            // keyword lists like "logistics freight cargo shipping" require every
            // single word to appear in the same article and almost always match
            // nothing. Group topic synonyms with OR instead.
            $topicQuery = match ($category) {
                'shipping' => '(shipping OR logistics OR port OR cargo)',
                'trade' => '("international trade" OR exports OR imports OR tariff)',
                'economy' => '(economy OR inflation OR gdp OR recession OR currency)',
                default => '("supply chain" OR logistics OR freight OR cargo OR shipping)'
            };

            $query = $countryName ? "{$topicQuery} AND {$countryName}" : $topicQuery;

            $response = Http::timeout(10)
                // Only retry on network/connection failures, not on HTTP error
                // responses like 429 (rate limit) or 403 (quota exceeded) -
                // retrying those just burns through the remaining daily quota faster.
                ->retry(2, 800, fn ($exception) => $exception instanceof ConnectionException)
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

            if ($response->status() === 403) {
                // 403 means the daily quota is exhausted; GNews only resets it at
                // 00:00 UTC, so a short fixed cooldown just causes repeated failed
                // retries all day. Wait until the actual UTC reset instead.
                $until = now('UTC')->addDay()->startOfDay();
                Cache::put(self::COOLDOWN_CACHE_KEY, $until->toIso8601String(), $until);
                Log::warning("GNews API daily quota exhausted (status 403). Pausing further requests until {$until->toIso8601String()} (00:00 UTC reset); serving existing cached data instead.");
            } elseif ($response->status() === 429) {
                $cooldownMinutes = (int) config('services.gnews.cooldown_minutes', 60);
                Cache::put(self::COOLDOWN_CACHE_KEY, now()->addMinutes($cooldownMinutes)->toIso8601String(), now()->addMinutes($cooldownMinutes));
                Log::warning("GNews API rate limit hit (status 429). Pausing further requests for {$cooldownMinutes} minutes; serving existing cached data instead.");
            } else {
                Log::warning("GNews API responded with status {$response->status()} for query '{$query}'.");
            }
        } catch (\Exception $e) {
            Log::warning("GNews API error: " . $e->getMessage());
        }

        return [];
    }
}
