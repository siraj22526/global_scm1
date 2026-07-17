<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'total_score', 'level', 'calculated_at'])]
class RiskScore extends Model
{
    protected $casts = [
        'total_score' => 'float',
        'calculated_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function components()
    {
        return $this->hasMany(RiskScoreComponent::class);
    }
}
