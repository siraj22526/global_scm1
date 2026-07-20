<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldPortIndexClient
{
    /**
     * Fetch all ports from the raw World Port Index JSON source.
     *
     * @return array
     */
    public function getPorts(): array
    {
        $url = 'https://raw.githubusercontent.com/tayljordan/ports/main/ports.json';

        try {
            $response = Http::timeout(15)
                ->retry(2, 200)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && isset($data['ports']) && is_array($data['ports'])) {
                    return $data['ports'];
                }
            } else {
                Log::warning("WorldPortIndexClient responded with status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("WorldPortIndexClient error: " . $e->getMessage());
        }

        return [];
    }
}
