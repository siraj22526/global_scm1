<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\WeatherSnapshot;
use App\Models\EconomicIndicator;
use App\Models\NewsCache;
use App\Models\SentimentResult;
use App\Models\RiskWeight;
use App\Services\RiskScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_scoring_calculation()
    {
        // 1. Setup country & currency
        $country = Country::create([
            'iso2' => 'DE',
            'iso3' => 'DEU',
            'name' => 'Germany',
            'official_name' => 'Federal Republic of Germany',
            'capital' => 'Berlin',
            'region' => 'Europe',
            'languages' => ['de' => 'German'],
            'flag_url' => '',
            'latitude' => 52.5200,
            'longitude' => 13.4050,
        ]);

        $currency = Currency::create([
            'country_id' => $country->id,
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€'
        ]);

        // 2. Setup weights in DB
        RiskWeight::create(['component' => 'weather', 'weight' => 0.30]);
        RiskWeight::create(['component' => 'news', 'weight' => 0.40]);
        RiskWeight::create(['component' => 'inflation', 'weight' => 0.20]);
        RiskWeight::create(['component' => 'currency', 'weight' => 0.10]);

        // 3. Seed weather snapshot
        // storm_risk = 50 (normalized weather risk = 50)
        WeatherSnapshot::create([
            'country_id' => $country->id,
            'temperature_c' => 20.0,
            'precipitation_mm' => 5.0,
            'wind_speed_kmh' => 25.0,
            'storm_risk' => 50,
            'recorded_at' => now(),
        ]);

        // 4. Seed news articles and sentiment
        // 3 articles total: 2 negative, 1 positive. Negative ratio = 2/3 = 66.67% (normalized news risk = 66.67)
        $art1 = NewsCache::create([
            'country_id' => $country->id,
            'title' => 'Crisis in shipping logistics',
            'url' => 'https://example.com/1',
            'category' => 'logistics',
            'published_at' => now()
        ]);
        SentimentResult::create(['news_id' => $art1->id, 'positive_score' => 0, 'negative_score' => 2, 'label' => 'negative']);

        $art2 = NewsCache::create([
            'country_id' => $country->id,
            'title' => 'Delays and risk warn at port',
            'url' => 'https://example.com/2',
            'category' => 'logistics',
            'published_at' => now()
        ]);
        SentimentResult::create(['news_id' => $art2->id, 'positive_score' => 0, 'negative_score' => 2, 'label' => 'negative']);

        $art3 = NewsCache::create([
            'country_id' => $country->id,
            'title' => 'Growth and profits rise',
            'url' => 'https://example.com/3',
            'category' => 'logistics',
            'published_at' => now()
        ]);
        SentimentResult::create(['news_id' => $art3->id, 'positive_score' => 2, 'negative_score' => 0, 'label' => 'positive']);

        // 5. Seed inflation indicator
        // Inflation = 6%. Normalized inflation risk: 20 + ((6-2)/8)*50 = 45% (normalized inflation risk = 45)
        EconomicIndicator::create([
            'country_id' => $country->id,
            'indicator' => 'inflation',
            'year' => 2026,
            'value' => 6.0,
            'fetched_at' => now()
        ]);

        // 6. Seed daily currency rates
        // Seed flat rates, CV = 0.0, normalized currency risk = 0
        CurrencyRate::create(['currency_id' => $currency->id, 'rate_to_usd' => 0.92, 'rate_date' => now()->toDateString()]);
        CurrencyRate::create(['currency_id' => $currency->id, 'rate_to_usd' => 0.92, 'rate_date' => now()->subDay()->toDateString()]);

        // 7. Calculate
        $service = new RiskScoringService();
        $risk = $service->calculateRiskForCountry($country);

        // Weather component score = 50, weight = 0.30 => 15
        // News component score = 66.67, weight = 0.40 => 26.67
        // Inflation component score = 45, weight = 0.20 => 9
        // Currency component score = 0, weight = 0.10 => 0
        // Expected weighted total = 15 + 26.67 + 9 + 0 = 50.67
        $this->assertEquals(50.67, (float) $risk->total_score);
        $this->assertEquals('medium', $risk->level);

        // Verify risk components saved
        $this->assertDatabaseHas('risk_score_components', [
            'risk_score_id' => $risk->id,
            'component' => 'weather',
            'normalized' => 50.00
        ]);
    }
}
