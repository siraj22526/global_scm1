<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['news_id', 'positive_score', 'negative_score', 'label'])]
class SentimentResult extends Model
{
    protected $casts = [
        'positive_score' => 'integer',
        'negative_score' => 'integer',
    ];

    public function newsCache()
    {
        return $this->belongsTo(NewsCache::class, 'news_id');
    }
}
