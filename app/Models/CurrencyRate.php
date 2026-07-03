<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['currency_id', 'rate_to_usd', 'rate_date'])]
class CurrencyRate extends Model
{
    protected $casts = [
        'rate_to_usd' => 'float',
        'rate_date' => 'date',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
