<?php

namespace App\Http\Controllers\Api;

use App\Models\Port;
use App\Models\Country;
use Illuminate\Http\Request;

class PortApiController extends ApiController
{
    /**
     * GET /api/ports
     */
    public function index(Request $request)
    {
        $queryText = $request->input('q');
        $countryIso = $request->input('country');
        $bbox = $request->input('bbox'); // Format: west,south,east,north (e.g. 100,-10,120,10)

        $portsQuery = Port::query()->with('country');

        if ($queryText) {
            $portsQuery->where(function ($q) use ($queryText) {
                $q->where('name', 'like', "%{$queryText}%")
                  ->orWhere('wpi_code', 'like', "%{$queryText}%");
            });
        }

        if ($countryIso) {
            $country = Country::where('iso2', strtoupper($countryIso))->first();
            if ($country) {
                $portsQuery->where('country_id', $country->id);
            }
        }

        if ($bbox) {
            $coords = explode(',', $bbox);
            if (count($coords) === 4) {
                $west = (float) $coords[0];
                $south = (float) $coords[1];
                $east = (float) $coords[2];
                $north = (float) $coords[3];

                // Standard bounding box query
                $portsQuery->whereBetween('longitude', [min($west, $east), max($west, $east)])
                           ->whereBetween('latitude', [min($south, $north), max($south, $north)]);
            }
        }

        // Limit results if no bounding box and no specific country filter to keep payload size low
        if (!$bbox && !$countryIso && !$queryText) {
            $portsQuery->limit(200);
        }

        $ports = $portsQuery->get();

        $data = $ports->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'wpi_code' => $p->wpi_code,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'harbor_size' => $p->harbor_size,
                'country' => [
                    'iso2' => $p->country->iso2,
                    'name' => $p->country->name
                ]
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * GET /api/ports/{id}
     */
    public function show(int $id)
    {
        $port = Port::with('country')->find($id);

        if (!$port) {
            return $this->sendError('PORT_NOT_FOUND', "Pelabuhan dengan ID {$id} tidak ditemukan", 404);
        }

        $data = [
            'id' => $port->id,
            'name' => $port->name,
            'wpi_code' => $port->wpi_code,
            'latitude' => (float) $port->latitude,
            'longitude' => (float) $port->longitude,
            'harbor_size' => $port->harbor_size,
            'country' => [
                'iso2' => $port->country->iso2,
                'name' => $port->country->name,
                'official_name' => $port->country->official_name
            ]
        ];

        return $this->sendResponse($data);
    }
}
