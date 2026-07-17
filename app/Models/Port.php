<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'name', 'wpi_code', 'latitude', 'longitude', 'harbor_size'])]
class Port extends Model
{
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
