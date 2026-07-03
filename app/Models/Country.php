<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['iso2', 'iso3', 'name', 'official_name', 'capital', 'region', 'languages', 'flag_url', 'latitude', 'longitude'])]
class Country extends Model
{
    protected $casts = [
        'languages' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function currency()
    {
        return $this->hasOne(Currency::class);
    }

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function economicIndicators()
    {
        return $this->hasMany(EconomicIndicator::class);
    }

    public function weatherSnapshots()
    {
        return $this->hasMany(WeatherSnapshot::class);
    }

    public function newsCaches()
    {
        return $this->hasMany(NewsCache::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }

    public function latestRiskScore()
    {
        return $this->hasOne(RiskScore::class)->latestOfMany('calculated_at');
    }
}
