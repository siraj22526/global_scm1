<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'indicator', 'year', 'value', 'fetched_at'])]
class EconomicIndicator extends Model
{
    protected $casts = [
        'year' => 'integer',
        'value' => 'float',
        'fetched_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
