<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\WeatherSnapshot;
use App\Models\NewsCache;
use App\Models\SentimentResult;
use App\Models\Watchlist;
use App\Integrations\ExchangeRateClient;
use App\Integrations\OpenMeteoClient;
use App\Integrations\GNewsClient;
use App\Services\SentimentService;
use App\Services\RiskScoringService;
use Illuminate\Console\Command;

class RefreshDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh weather, currencies, news, and re-calculate all country supply chain risk scores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled data refresh and risk re-calculation...');

        $countries = Country::all();
        $this->info("Found {$countries->count()} countries in the database.");

        // 1. Sync Currency rates vs USD
        $this->info('Syncing exchange rates...');
        $rateClient = new ExchangeRateClient();
        $rates = $rateClient->getLatestRates();
        $today = now()->toDateString();
        
        if (!empty($rates)) {
            $currencies = Currency::all();
            foreach ($currencies as $curr) {
                if (isset($rates[$curr->code])) {
                    CurrencyRate::updateOrCreate(
                        ['currency_id' => $curr->id, 'rate_date' => $today],
                        ['rate_to_usd' => (float) $rates[$curr->code]]
                    );
                }
            }
            $this->info('Exchange rates synced successfully.');
        } else {
            $this->error('Failed to retrieve exchange rates.');
        }

        // 2. Fetch and calculate metrics per country
        $weatherClient = new OpenMeteoClient();
        $gnewsClient = new GNewsClient();
        $sentimentService = new SentimentService();
        $riskService = new RiskScoringService();

        foreach ($countries as $index => $country) {
            $this->comment("Processing ({$country->iso2}) - {$country->name}...");

            // A. Fetch Weather Snapshot
            $weather = $weatherClient->getWeather($country->latitude, $country->longitude);
            if ($weather) {
                WeatherSnapshot::create([
                    'country_id' => $country->id,
                    'temperature_c' => $weather['temperature_c'],
                    'precipitation_mm' => $weather['precipitation_mm'],
                    'wind_speed_kmh' => $weather['wind_speed_kmh'],
                    'storm_risk' => $weather['storm_risk'],
                    'recorded_at' => now(),
                ]);
            }

            // B. Fetch news and calculate sentiment
            foreach (['logistics', 'shipping', 'trade', 'economy'] as $cat) {
                $newsList = $gnewsClient->getNews($cat, $country->iso2);
                foreach ($newsList as $item) {
                    try {
                        $news = NewsCache::updateOrCreate(
                            ['url' => $item['url']],
                            [
                                'country_id' => $country->id,
                                'title' => substr($item['title'], 0, 255),
                                'description' => $item['description'],
                                'image_url' => $item['image_url'] ?? null,
                                'category' => $cat,
                                'published_at' => is_string($item['published_at']) ? now()->parse($item['published_at']) : $item['published_at'],
                            ]
                        );

                        $sent = $sentimentService->analyze($item['title'], $item['description']);
                        SentimentResult::updateOrCreate(
                            ['news_id' => $news->id],
                            [
                                'positive_score' => $sent['positive_score'],
                                'negative_score' => $sent['negative_score'],
                                'label' => $sent['label']
                            ]
                        );
                    } catch (\Exception $e) {
                        // skip unique url conflicts
                    }
                }
            }

            // C. Calculate Weighted Risk Score
            $risk = $riskService->calculateRiskForCountry($country);
            $this->info("Calculated risk score for {$country->name}: {$risk->total_score} ({$risk->level})");
        }

        // 3. Clean up task (Retention Strategy Section 8.6)
        // Clean snapshots > 90 days and news_cache > 60 days
        $this->info('Running retention policy cleanup...');
        WeatherSnapshot::where('recorded_at', '<', now()->subDays(90))->delete();
        NewsCache::where('published_at', '<', now()->subDays(60))->delete();
        $this->info('Cleanup completed.');

        $this->info('All scheduled data refresh operations finished successfully!');
        return self::SUCCESS;
    }
}
