<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Integrations\ExchangeRateClient;
use Illuminate\Http\Request;

class CurrencyApiController extends ApiController
{
    /**
     * GET /api/currency
     */
    public function index()
    {
        // Check if we have currency rates for today
        $today = now()->toDateString();
        $rateExists = CurrencyRate::where('rate_date', $today)->exists();

        if (!$rateExists) {
            $client = new ExchangeRateClient();
            $rates = $client->getLatestRates();

            if (!empty($rates)) {
                $currencies = Currency::all();
                foreach ($currencies as $curr) {
                    $code = $curr->code;
                    // Rates returned are vs USD (e.g. EUR => 0.92, which means 1 USD = 0.92 EUR)
                    // Or we can store conversion directly
                    if (isset($rates[$code])) {
                        CurrencyRate::updateOrCreate(
                            [
                                'currency_id' => $curr->id,
                                'rate_date' => $today,
                            ],
                            [
                                'rate_to_usd' => (float) $rates[$code],
                            ]
                        );
                    }
                }
            }
        }

        // Return latest rate per currency
        $currencies = Currency::with(['rates' => function ($query) {
            $query->orderBy('rate_date', 'desc')->limit(1);
        }])->get();

        $data = $currencies->map(function ($c) {
            $latest = $c->rates->first();
            return [
                'code' => $c->code,
                'name' => $c->name,
                'symbol' => $c->symbol,
                'rate_to_usd' => $latest ? (float) $latest->rate_to_usd : null,
                'updated_at' => $latest ? $latest->updated_at->toIso8601String() : null,
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * GET /api/currency/{code}/history
     */
    public function history(Request $request, string $code)
    {
        $code = strtoupper($code);
        $currency = Currency::where('code', $code)->first();

        if (!$currency) {
            return $this->sendError('CURRENCY_NOT_FOUND', "Mata uang {$code} tidak ditemukan", 404);
        }

        $days = (int) $request->input('days', 30);
        $history = CurrencyRate::where('currency_id', $currency->id)
            ->where('rate_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('rate_date', 'asc')
            ->get();

        $data = $history->map(function ($h) {
            return [
                'rate_to_usd' => (float) $h->rate_to_usd,
                'rate_date' => $h->rate_date->toDateString(),
            ];
        });

        return $this->sendResponse($data);
    }
}
