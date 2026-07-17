<?php

namespace App\Http\Controllers\Api;

use App\Models\Watchlist;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistApiController extends ApiController
{
    /**
     * GET /api/watchlist
     */
    public function index()
    {
        $userId = Auth::id();
        
        $watchlist = Watchlist::where('user_id', $userId)
            ->with(['country.latestRiskScore'])
            ->get();

        $data = $watchlist->map(function ($w) {
            $c = $w->country;
            return [
                'iso2' => $c->iso2,
                'name' => $c->name,
                'capital' => $c->capital,
                'flag_url' => $c->flag_url,
                'risk' => $c->latestRiskScore ? [
                    'total_score' => (float) $c->latestRiskScore->total_score,
                    'level' => $c->latestRiskScore->level,
                ] : null
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * POST /api/watchlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'country_iso' => ['required', 'string', 'max:3'],
        ]);

        $userId = Auth::id();
        $iso = strtoupper($request->input('country_iso'));

        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();
        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        // Check duplicates
        $exists = Watchlist::where('user_id', $userId)
            ->where('country_id', $country->id)
            ->exists();

        if ($exists) {
            return $this->sendError('WATCHLIST_DUPLICATE', 'Negara ini sudah ada di daftar pantauan Anda.', 409);
        }

        // Limit maximum 20 countries
        $count = Watchlist::where('user_id', $userId)->count();
        if ($count >= 20) {
            return $this->sendError('WATCHLIST_LIMIT_REACHED', 'Batas maksimal daftar pantauan adalah 20 negara.', 422);
        }

        $watchlist = Watchlist::create([
            'user_id' => $userId,
            'country_id' => $country->id
        ]);

        return $this->sendResponse([
            'country_iso' => $country->iso2,
            'added_at' => $watchlist->created_at->toIso8601String()
        ], 'Negara berhasil ditambahkan ke daftar pantauan.', 201);
    }

    /**
     * DELETE /api/watchlist/{iso}
     */
    public function destroy(string $iso)
    {
        $userId = Auth::id();
        $iso = strtoupper($iso);

        $country = Country::where('iso2', $iso)->orWhere('iso3', $iso)->first();
        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', "Negara {$iso} tidak ditemukan", 404);
        }

        $watchlist = Watchlist::where('user_id', $userId)
            ->where('country_id', $country->id)
            ->first();

        if (!$watchlist) {
            return $this->sendError('WATCHLIST_NOT_FOUND', 'Negara tidak ditemukan dalam daftar pantauan Anda.', 404);
        }

        $watchlist->delete();

        return $this->sendResponse([
            'country_iso' => $country->iso2
        ], 'Negara berhasil dihapus dari daftar pantauan.');
    }
}
