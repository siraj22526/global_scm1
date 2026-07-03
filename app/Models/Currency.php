<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'code', 'name', 'symbol'])]
class Currency extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function rates()
    {
        return $this->hasMany(CurrencyRate::class);
    }
}
