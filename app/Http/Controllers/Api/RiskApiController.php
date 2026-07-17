<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class RiskApiController extends ApiController
{
    /**
     * GET /api/risk
     */
    public function index()
    {
        $countries = Country::with('latestRiskScore')->get();
        
        $data = $countries->map(function ($c) {
            return [
                'iso2' => $c->iso2,
                'name' => $c->name,
                'total_score' => $c->latestRiskScore ? (float) $c->latestRiskScore->total_score : null,
                'level' => $c->latestRiskScore ? $c->latestRiskScore->level : null,
                'calculated_at' => $c->latestRiskScore ? $c->latestRiskScore->calculated_at->toIso8601String() : null,
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * GET /api/risk/{iso}
     */
    public function show(string $iso)
    {
        $iso = strtoupper($iso);
        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();

        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $latestRisk = RiskScore::where('country_id', $country->id)
            ->with('components')
            ->orderBy('calculated_at', 'desc')
            ->first();

        if (!$latestRisk) {
            return $this->sendError('RISK_NOT_CALCULATED', "Skor risiko belum dihitung untuk {$country->name}", 404);
        }

        $components = $latestRisk->components->map(function ($comp) {
            return [
                'component' => $comp->component,
                'raw_value' => (float) $comp->raw_value,
                'normalized' => (float) $comp->normalized,
                'weight' => (float) $comp->weight,
            ];
        });

        $data = [
            'country' => $country->iso2,
            'total_score' => (float) $latestRisk->total_score,
            'level' => $latestRisk->level,
            'calculated_at' => $latestRisk->calculated_at->toIso8601String(),
            'components' => $components
        ];

        return $this->sendResponse($data);
    }

    /**
     * GET /api/risk/{iso}/history
     */
    public function history(Request $request, string $iso)
    {
        $iso = strtoupper($iso);
        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();

        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $days = (int) $request->input('days', 30);
        $history = RiskScore::where('country_id', $country->id)
            ->where('calculated_at', '>=', now()->subDays($days))
            ->orderBy('calculated_at', 'asc')
            ->get();

        $data = $history->map(function ($h) {
            return [
                'total_score' => (float) $h->total_score,
                'level' => $h->level,
                'calculated_at' => $h->calculated_at->toIso8601String(),
            ];
        });

        return $this->sendResponse($data);
    }
}
