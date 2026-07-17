<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoClient
{
    public function getWeather(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,precipitation,wind_speed_10m',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];

                $temp = (float) ($current['temperature_2m'] ?? 0.0);
                $precipitation = (float) ($current['precipitation'] ?? 0.0);
                $windSpeed = (float) ($current['wind_speed_10m'] ?? 0.0);

                // Calculate storm risk logic:
                // Wind speed contributes up to 50% (cap at 80 km/h)
                // Precipitation contributes up to 50% (cap at 50 mm)
                $windFactor = min($windSpeed / 80.0, 1.0) * 50;
                $rainFactor = min($precipitation / 50.0, 1.0) * 50;
                $stormRisk = (int) round($windFactor + $rainFactor);

                return [
                    'temperature_c' => $temp,
                    'precipitation_mm' => $precipitation,
                    'wind_speed_kmh' => $windSpeed,
                    'storm_risk' => $stormRisk,
                ];
            }
        } catch (\Exception $e) {
            Log::error("OpenMeteoClient error for lat={$latitude}, lng={$longitude}: " . $e->getMessage());
        }

        return null;
    }
}
