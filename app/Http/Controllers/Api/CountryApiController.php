<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Models\WeatherSnapshot;
use App\Integrations\RESTCountriesClient;
use App\Integrations\WorldBankClient;
use App\Integrations\OpenMeteoClient;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CountryApiController extends ApiController
{
    /**
     * GET /api/countries
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        $countries = Country::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('iso2', 'like', "%{$query}%")
              ->orWhere('official_name', 'like', "%{$query}%");
        })->get();

        return $this->sendResponse($countries);
    }

    /**
     * GET /api/countries/{iso}
     */
    public function show(string $iso)
    {
        $iso = strtoupper($iso);
        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();

        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $cached = true;
        $fetchedAt = now();

        // 1. Ensure Economic Indicators are loaded (Cache 7 days)
        $latestIndicator = EconomicIndicator::where('country_id', $country->id)->first();
        if (!$latestIndicator || $latestIndicator->updated_at->addDays(7)->isPast()) {
            $cached = false;
            $wbClient = new WorldBankClient();
            foreach (['gdp', 'inflation', 'population', 'export', 'import'] as $ind) {
                $data = $wbClient->getIndicatorData($country->iso2, $ind);
                foreach ($data as $entry) {
                    EconomicIndicator::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'indicator' => $ind,
                            'year' => $entry['year']
                        ],
                        [
                            'value' => $entry['value'],
                            'fetched_at' => now()
                        ]
                    );
                }
            }
        }

        // 2. Ensure Weather Snapshot is loaded (Cache 30 minutes)
        $latestWeather = WeatherSnapshot::where('country_id', $country->id)
            ->orderBy('recorded_at', 'desc')
            ->first();
        if (!$latestWeather || $latestWeather->recorded_at->addMinutes(30)->isPast()) {
            $cached = false;
            $openMeteo = new OpenMeteoClient();
            $weatherData = $openMeteo->getWeather($country->latitude, $country->longitude);
            if ($weatherData) {
                $latestWeather = WeatherSnapshot::create([
                    'country_id' => $country->id,
                    'temperature_c' => $weatherData['temperature_c'],
                    'precipitation_mm' => $weatherData['precipitation_mm'],
                    'wind_speed_kmh' => $weatherData['wind_speed_kmh'],
                    'storm_risk' => $weatherData['storm_risk'],
                    'recorded_at' => now(),
                ]);
            }
        }

        // 3. Ensure Risk Score is calculated
        $latestRisk = $country->latestRiskScore;
        if (!$latestRisk || $latestRisk->calculated_at->addHour()->isPast() || !$cached) {
            $riskScoring = new RiskScoringService();
            $latestRisk = $riskScoring->calculateRiskForCountry($country);
        }

        // Fetch latest details
        $country->load('currency');
        $gdp = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'gdp')->orderBy('year', 'desc')->first();
        $inflation = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'inflation')->orderBy('year', 'desc')->first();
        $population = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'population')->orderBy('year', 'desc')->first();

        $data = [
            'iso2' => $country->iso2,
            'name' => $country->name,
            'capital' => $country->capital,
            'region' => $country->region,
            'currency' => [
                'code' => $country->currency->code ?? 'USD',
                'symbol' => $country->currency->symbol ?? '$'
            ],
            'population' => $population ? (int) $population->value : null,
            'gdp_usd' => $gdp ? (float) $gdp->value : null,
            'inflation_pct' => $inflation ? (float) $inflation->value : null,
            'weather' => $latestWeather ? [
                'temperature_c' => (float) $latestWeather->temperature_c,
                'precipitation_mm' => (float) $latestWeather->precipitation_mm,
                'wind_speed_kmh' => (float) $latestWeather->wind_speed_kmh,
            ] : null,
            'risk' => $latestRisk ? [
                'total_score' => (float) $latestRisk->total_score,
                'level' => $latestRisk->level
            ] : null,
        ];

        $meta = [
            'cached' => $cached,
            'fetched_at' => $fetchedAt->toIso8601String()
        ];

        return $this->sendResponse($data, '', 200, $meta);
    }

    /**
     * GET /api/countries/{iso}/indicators
     */
    public function indicators(string $iso)
    {
        $iso = strtoupper($iso);
        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();

        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $indicators = EconomicIndicator::where('country_id', $country->id)
            ->orderBy('year', 'asc')
            ->get()
            ->groupBy('indicator');

        return $this->sendResponse($indicators);
    }

    /**
     * GET /api/countries/{iso}/weather
     */
    public function weather(string $iso)
    {
        $iso = strtoupper($iso);
        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();

        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $weather = WeatherSnapshot::where('country_id', $country->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(10)
            ->get();

        return $this->sendResponse($weather);
    }
}
