<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['country_id', 'title', 'description', 'url', 'image_url', 'category', 'published_at'])]
class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function sentimentResult()
    {
        return $this->hasOne(SentimentResult::class, 'news_id');
    }
}
