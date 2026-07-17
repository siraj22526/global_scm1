<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['component', 'weight'])]
class RiskWeight extends Model
{
    protected $casts = [
        'weight' => 'float',
    ];
}
