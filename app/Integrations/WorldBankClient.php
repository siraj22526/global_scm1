<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldBankClient
{
    protected $indicators = [
        'gdp' => 'NY.GDP.MKTP.CD',
        'inflation' => 'FP.CPI.TOTL.ZG',
        'population' => 'SP.POP.TOTL',
        'export' => 'NE.EXP.GNFS.CD',
        'import' => 'NE.IMP.GNFS.CD'
    ];

    public function getIndicatorData(string $iso2, string $indicator): array
    {
        $indicatorCode = $this->indicators[$indicator] ?? null;
        if (!$indicatorCode) {
            return [];
        }

        try {
            // Fetch last 15 years to get a good trend
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get("https://api.worldbank.org/v2/country/{$iso2}/indicator/{$indicatorCode}", [
                    'format' => 'json',
                    'per_page' => 15,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && count($data) > 1 && is_array($data[1])) {
                    $results = [];
                    foreach ($data[1] as $item) {
                        $year = (int) ($item['date'] ?? 0);
                        $value = $item['value'];
                        if ($year && $value !== null) {
                            $results[] = [
                                'year' => $year,
                                'value' => (float) $value
                            ];
                        }
                    }
                    return $results;
                }
            }
        } catch (\Exception $e) {
            Log::error("WorldBankClient error for {$iso2} indicator {$indicator}: " . $e->getMessage());
        }

        return [];
    }
}
