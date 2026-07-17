<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\SentimentResult;
use App\Integrations\GNewsClient;
use App\Services\SentimentService;
use Illuminate\Http\Request;

class NewsApiController extends ApiController
{
    /**
     * GET /api/news
     */
    public function index(Request $request)
    {
        $category = $request->input('category', 'logistics');
        $countryIso = $request->input('country');

        $country = null;
        if ($countryIso) {
            $country = Country::where('iso2', strtoupper($countryIso))->orWhere('iso3', strtoupper($countryIso))->first();
        }

        $countryId = $country ? $country->id : null;

        // Check cache in database (stale after 1 hour)
        $oneHourAgo = now()->subHour();
        $cacheQuery = NewsCache::where('category', $category)
            ->where('country_id', $countryId);

        $cachedArticlesCount = $cacheQuery->count();
        $latestArticle = $cacheQuery->orderBy('created_at', 'desc')->first();

        // If missing or stale, fetch from client
        if ($cachedArticlesCount === 0 || ($latestArticle && $latestArticle->created_at->isBefore($oneHourAgo))) {
            $gnews = new GNewsClient();
            $sentimentService = new SentimentService();
            $fetchedNews = $gnews->getNews($category, $country ? $country->iso2 : null);

            if (!empty($fetchedNews)) {
                // Delete old cache for this category & country to avoid piling up
                NewsCache::where('category', $category)
                    ->where('country_id', $countryId)
                    ->delete();

                foreach ($fetchedNews as $item) {
                    try {
                        $news = NewsCache::updateOrCreate(
                            ['url' => $item['url']],
                            [
                                'country_id' => $countryId,
                                'title' => substr($item['title'], 0, 255),
                                'description' => $item['description'],
                                'image_url' => $item['image_url'] ?? null,
                                'category' => $category,
                                'published_at' => is_string($item['published_at']) ? now()->parse($item['published_at']) : $item['published_at'],
                            ]
                        );

                        // Run lexicon analysis
                        $sentiment = $sentimentService->analyze($item['title'], $item['description']);

                        SentimentResult::updateOrCreate(
                            ['news_id' => $news->id],
                            [
                                'positive_score' => $sentiment['positive_score'],
                                'negative_score' => $sentiment['negative_score'],
                                'label' => $sentiment['label']
                            ]
                        );
                    } catch (\Exception $e) {
                        // ignore unique constraint collisions or formatting issues
                    }
                }
            }
        }

        // Return cached news with sentiment
        $articles = NewsCache::where('category', $category)
            ->where('country_id', $countryId)
            ->with('sentimentResult')
            ->orderBy('published_at', 'desc')
            ->get();

        $data = $articles->map(function ($art) {
            return [
                'title' => $art->title,
                'description' => $art->description,
                'url' => $art->url,
                'image_url' => $art->image_url,
                'category' => $art->category,
                'published_at' => $art->published_at->toIso8601String(),
                'sentiment' => $art->sentimentResult ? [
                    'positive' => (int) $art->sentimentResult->positive_score,
                    'negative' => (int) $art->sentimentResult->negative_score,
                    'label' => $art->sentimentResult->label,
                ] : null
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * GET /api/news/summary
     */
    public function summary(Request $request)
    {
        $category = $request->input('category');
        $countryIso = $request->input('country');

        $country = null;
        if ($countryIso) {
            $country = Country::where('iso2', strtoupper($countryIso))->first();
        }

        $query = NewsCache::query();
        if ($category) {
            $query->where('category', $category);
        }
        if ($country) {
            $query->where('country_id', $country->id);
        } else {
            $query->whereNull('country_id');
        }

        $articles = $query->pluck('id');
        $sentiments = SentimentResult::whereIn('news_id', $articles)->pluck('label')->toArray();
        $total = count($sentiments);

        if ($total === 0) {
            return $this->sendResponse([
                'positive_pct' => 0,
                'neutral_pct' => 0,
                'negative_pct' => 0,
                'total' => 0
            ]);
        }

        $counts = array_count_values($sentiments);
        $positive = $counts['positive'] ?? 0;
        $neutral = $counts['neutral'] ?? 0;
        $negative = $counts['negative'] ?? 0;

        return $this->sendResponse([
            'positive_pct' => round(($positive / $total) * 100, 1),
            'neutral_pct' => round(($neutral / $total) * 100, 1),
            'negative_pct' => round(($negative / $total) * 100, 1),
            'total' => $total
        ]);
    }
}
