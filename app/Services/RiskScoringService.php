<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\RiskScoreComponent;
use App\Models\RiskWeight;
use App\Models\WeatherSnapshot;
use App\Models\EconomicIndicator;
use App\Models\NewsCache;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    /**
     * Calculate and store the risk score for a country.
     */
    public function calculateRiskForCountry(Country $country): RiskScore
    {
        // 1. Weather Risk (0-100)
        // Latest weather snapshot
        $latestWeather = WeatherSnapshot::where('country_id', $country->id)
            ->orderBy('recorded_at', 'desc')
            ->first();
        $weatherRaw = $latestWeather ? (float) $latestWeather->storm_risk : 50.0;
        $weatherNorm = $weatherRaw; // storm_risk is already 0-100

        // 2. Political News Risk (0-100)
        // Ratio of negative articles in country news. If none, fall back to global news.
        $newsQuery = NewsCache::where('country_id', $country->id);
        if ($newsQuery->count() === 0) {
            $newsQuery = NewsCache::whereNull('country_id');
        }
        $newsArticles = $newsQuery->with('sentimentResult')->get();
        $totalNews = 0;
        $negativeNews = 0;

        foreach ($newsArticles as $art) {
            if ($art->sentimentResult) {
                $totalNews++;
                if ($art->sentimentResult->label === 'negative') {
                    $negativeNews++;
                }
            }
        }

        $newsRaw = $totalNews > 0 ? ($negativeNews / $totalNews) * 100 : 50.0;
        $newsNorm = $newsRaw;

        // 3. Inflation Risk (0-100)
        // Fetch latest inflation indicator
        $inflationIndicator = EconomicIndicator::where('country_id', $country->id)
            ->where('indicator', 'inflation')
            ->orderBy('year', 'desc')
            ->first();
        $inflationRaw = $inflationIndicator ? (float) $inflationIndicator->value : 2.0; // default 2%
        
        // Map inflation rate to 0-100
        $inflationNorm = $this->normalizeInflation($inflationRaw);

        // 4. Currency Risk (0-100)
        // USD is base, so volatility against itself is 0
        $currencyNorm = 0.0;
        $currencyRaw = 0.0;

        if ($country->currency && $country->currency->code !== 'USD') {
            // Fetch rates over last 30 days
            $rates = CurrencyRate::where('currency_id', $country->currency->id)
                ->orderBy('rate_date', 'desc')
                ->limit(30)
                ->pluck('rate_to_usd')
                ->toArray();

            if (count($rates) >= 2) {
                $currencyRaw = $this->calculateVolatility($rates);
                // Volatility is mapped to 0-100 (CV of 5% or 0.05 is considered very high)
                $currencyNorm = min(($currencyRaw / 0.05) * 100, 100.0);
            } else {
                $currencyNorm = 30.0; // default/neutral low volatility
                $currencyRaw = 0.015;
            }
        }

        // Fetch Weights from DB
        $dbWeights = RiskWeight::all()->pluck('weight', 'component')->toArray();
        $wWeather = $dbWeights['weather'] ?? 0.30;
        $wNews = $dbWeights['news'] ?? 0.40;
        $wInflation = $dbWeights['inflation'] ?? 0.20;
        $wCurrency = $dbWeights['currency'] ?? 0.10;

        // Sum weights to normalize in case they don't sum to 1.0
        $totalWeight = $wWeather + $wNews + $wInflation + $wCurrency;
        if ($totalWeight <= 0) {
            $totalWeight = 1.0;
        }

        $weightedScore = (
            ($weatherNorm * $wWeather) +
            ($newsNorm * $wNews) +
            ($inflationNorm * $wInflation) +
            ($currencyNorm * $wCurrency)
        ) / $totalWeight;

        $totalScore = round(max(0, min($weightedScore, 100.0)), 2);

        // Map total score to level:
        // 0-33: low, 34-66: medium, 67-100: high
        $level = 'low';
        if ($totalScore >= 67) {
            $level = 'high';
        } elseif ($totalScore >= 34) {
            $level = 'medium';
        }

        return DB::transaction(function () use ($country, $totalScore, $level, $weatherRaw, $weatherNorm, $wWeather, $newsRaw, $newsNorm, $wNews, $inflationRaw, $inflationNorm, $wInflation, $currencyRaw, $currencyNorm, $wCurrency) {
            // Save to risk_scores
            $riskScore = RiskScore::create([
                'country_id' => $country->id,
                'total_score' => $totalScore,
                'level' => $level,
                'calculated_at' => now(),
            ]);

            // Save components
            $components = [
                ['component' => 'weather', 'raw_value' => $weatherRaw, 'normalized' => $weatherNorm, 'weight' => $wWeather],
                ['component' => 'news', 'raw_value' => $newsRaw, 'normalized' => $newsNorm, 'weight' => $wNews],
                ['component' => 'inflation', 'raw_value' => $inflationRaw, 'normalized' => $inflationNorm, 'weight' => $wInflation],
                ['component' => 'currency', 'raw_value' => $currencyRaw, 'normalized' => $currencyNorm, 'weight' => $wCurrency],
            ];

            foreach ($components as $comp) {
                RiskScoreComponent::create([
                    'risk_score_id' => $riskScore->id,
                    'component' => $comp['component'],
                    'raw_value' => $comp['raw_value'],
                    'normalized' => $comp['normalized'],
                    'weight' => $comp['weight'],
                ]);
            }

            return $riskScore;
        });
    }

    /**
     * Map annual inflation rate (in %) to a scale of 0 to 100.
     */
    protected function normalizeInflation(float $inflation): float
    {
        if ($inflation <= 0.0) {
            return 10.0; // slight deflation risk
        }
        if ($inflation <= 2.0) {
            return 20.0; // perfect inflation
        }
        if ($inflation <= 10.0) {
            // Scale linearly between 2% (20 points) and 10% (70 points)
            return 20.0 + (($inflation - 2.0) / 8.0) * 50.0;
        }
        // Inflation above 10%: scale linearly up to 20% (100 points)
        return 70.0 + min((($inflation - 10.0) / 10.0) * 30.0, 30.0);
    }

    /**
     * Calculate Coefficient of Variation (volatility) of historical exchange rates.
     */
    protected function calculateVolatility(array $rates): float
    {
        $count = count($rates);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($rates) / $count;
        if ($mean <= 0) {
            return 0.0;
        }

        $variance = 0.0;
        foreach ($rates as $r) {
            $variance += pow($r - $mean, 2);
        }
        $variance /= ($count - 1);
        $stdDev = sqrt($variance);

        return $stdDev / $mean; // Coefficient of Variation
    }
}
