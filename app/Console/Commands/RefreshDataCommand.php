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
use App\Integrations\RESTCountriesClient;
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

        // 0. Auto-sync WPI ports if database is empty
        if (\App\Models\Port::count() === 0) {
            $this->info('Tabel pelabuhan kosong. Melakukan sinkronisasi otomatis dari API WPI...');
            $wpiClient = new \App\Integrations\WorldPortIndexClient();
            $portsData = $wpiClient->getPorts();
            if (!empty($portsData)) {
                $countriesMap = $countries->keyBy(function ($c) {
                    return strtolower($c->name);
                });
                $imported = 0;
                \Illuminate\Support\Facades\DB::transaction(function () use ($portsData, $countriesMap, &$imported) {
                    foreach ($portsData as $item) {
                        $portCountry = $item['country'] ?? '';
                        $countryKey = strtolower(trim($portCountry));
                        if ($countriesMap->has($countryKey)) {
                            $country = $countriesMap->get($countryKey);
                            $pName = $item['wpi_port_name'] ?? '';
                            $pLat = $item['latitude'] ?? null;
                            $pLng = $item['longitude'] ?? null;
                            $pWpi = $item['wpi_port_id'] ?? null;
                            $pSize = $item['port_size'] ?? null;

                            if (!empty($pName) && $pLat !== null && $pLng !== null) {
                                \App\Models\Port::updateOrCreate(
                                    [
                                        'country_id' => $country->id,
                                        'name' => trim($pName)
                                    ],
                                    [
                                        'wpi_code' => $pWpi ? (string) $pWpi : null,
                                        'latitude' => (float) $pLat,
                                        'longitude' => (float) $pLng,
                                        'harbor_size' => $pSize ? trim($pSize) : null
                                    ]
                                );
                                $imported++;
                            }
                        }
                    }
                });
                $this->info("Sinkronisasi otomatis pelabuhan berhasil: {$imported} pelabuhan diimpor.");
            } else {
                $this->error('Gagal mengambil data pelabuhan dari API WPI.');
            }
        }

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
        $restCountriesClient = new RESTCountriesClient();
        $sentimentService = new SentimentService();
        $riskService = new RiskScoringService();

        // News is fetched globally (not per-country) to stay within GNews' free-tier
        // daily quota: 4 category requests per run instead of 4 x number-of-countries.
        // Per-country news is fetched on demand and cached by NewsApiController when
        // a user actually opens that country's news feed.
        $this->info('Fetching global news and calculating sentiment...');
        foreach (['logistics', 'shipping', 'trade', 'economy'] as $cat) {
            $newsList = $gnewsClient->getNews($cat, null);
            foreach ($newsList as $item) {
                try {
                    $news = NewsCache::updateOrCreate(
                        ['url' => $item['url']],
                        [
                            'country_id' => null,
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

        foreach ($countries as $index => $country) {
            $this->comment("Processing ({$country->iso2}) - {$country->name}...");

            // A0. Refresh country profile (name, capital, region, languages, flag, currency) from REST Countries API (every 30 days)
            if ($country->updated_at->addDays(30)->isPast()) {
                $profile = $restCountriesClient->getCountry($country->iso2);
                if ($profile) {
                    $country->update([
                        'name' => $profile['name'] ?: $country->name,
                        'official_name' => $profile['official_name'] ?: $country->official_name,
                        'capital' => $profile['capital'] ?: $country->capital,
                        'region' => $profile['region'] ?: $country->region,
                        'languages' => $profile['languages'] ?: $country->languages,
                        'flag_url' => $profile['flag_url'] ?: $country->flag_url,
                    ]);

                    if ($profile['currency_code']) {
                        Currency::updateOrCreate(
                            ['country_id' => $country->id],
                            [
                                'code' => $profile['currency_code'],
                                'name' => $profile['currency_name'],
                                'symbol' => $profile['currency_symbol'],
                            ]
                        );
                    }
                    $this->info("Profil negara {$country->name} diperbarui dari REST Countries API.");
                }
            }

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
