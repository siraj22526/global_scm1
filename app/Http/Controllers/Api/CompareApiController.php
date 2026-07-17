<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Models\WeatherSnapshot;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;

class CompareApiController extends ApiController
{
    /**
     * GET /api/compare?a=DE&b=AU
     */
    public function compare(Request $request)
    {
        $isoA = strtoupper($request->input('a'));
        $isoB = strtoupper($request->input('b'));

        if (!$isoA || !$isoB) {
            return $this->sendError('MISSING_PARAMETERS', 'Silakan pilih kedua negara untuk dibandingkan.', 422);
        }

        $countryA = Country::where('iso2', $isoA)->orWhere('iso3', $isoA)->first();
        $countryB = Country::where('iso2', $isoB)->orWhere('iso3', $isoB)->first();

        if (!$countryA || !$countryB) {
            return $this->sendError('COUNTRY_NOT_FOUND', 'Salah satu atau kedua negara tidak ditemukan.', 404);
        }

        // Get details helper
        $dataA = $this->getCountryDataForComparison($countryA);
        $dataB = $this->getCountryDataForComparison($countryB);

        return $this->sendResponse([
            'country_a' => $dataA,
            'country_b' => $dataB
        ]);
    }

    protected function getCountryDataForComparison(Country $country): array
    {
        // Check weather / risk stale
        $latestWeather = WeatherSnapshot::where('country_id', $country->id)->orderBy('recorded_at', 'desc')->first();
        $latestRisk = $country->latestRiskScore;

        if (!$latestRisk || $latestRisk->calculated_at->addHour()->isPast()) {
            $riskScoring = new RiskScoringService();
            $latestRisk = $riskScoring->calculateRiskForCountry($country);
        }

        $gdp = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'gdp')->orderBy('year', 'desc')->first();
        $inflation = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'inflation')->orderBy('year', 'desc')->first();
        $population = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'population')->orderBy('year', 'desc')->first();
        $export = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'export')->orderBy('year', 'desc')->first();
        $import = EconomicIndicator::where('country_id', $country->id)->where('indicator', 'import')->orderBy('year', 'desc')->first();

        $country->load('currency');

        return [
            'iso2' => $country->iso2,
            'name' => $country->name,
            'capital' => $country->capital,
            'region' => $country->region,
            'currency' => [
                'code' => $country->currency->code ?? 'USD',
                'symbol' => $country->currency->symbol ?? '$'
            ],
            'gdp' => $gdp ? (float) $gdp->value : null,
            'inflation' => $inflation ? (float) $inflation->value : null,
            'population' => $population ? (int) $population->value : null,
            'export' => $export ? (float) $export->value : null,
            'import' => $import ? (float) $import->value : null,
            'weather' => $latestWeather ? [
                'temperature_c' => (float) $latestWeather->temperature_c,
                'precipitation_mm' => (float) $latestWeather->precipitation_mm,
                'wind_speed_kmh' => (float) $latestWeather->wind_speed_kmh,
                'storm_risk' => (int) $latestWeather->storm_risk
            ] : null,
            'risk' => $latestRisk ? [
                'total_score' => (float) $latestRisk->total_score,
                'level' => $latestRisk->level
            ] : null
        ];
    }
}
