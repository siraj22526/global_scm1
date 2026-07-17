<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'temperature_c', 'precipitation_mm', 'wind_speed_kmh', 'storm_risk', 'recorded_at'])]
class WeatherSnapshot extends Model
{
    protected $casts = [
        'temperature_c' => 'float',
        'precipitation_mm' => 'float',
        'wind_speed_kmh' => 'float',
        'storm_risk' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
