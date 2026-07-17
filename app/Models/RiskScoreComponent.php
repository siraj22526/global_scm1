<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['risk_score_id', 'component', 'raw_value', 'normalized', 'weight'])]
class RiskScoreComponent extends Model
{
    protected $casts = [
        'raw_value' => 'float',
        'normalized' => 'float',
        'weight' => 'float',
    ];

    public function riskScore()
    {
        return $this->belongsTo(RiskScore::class);
    }
}
