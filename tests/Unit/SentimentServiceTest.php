<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SentimentService;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class SentimentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lexicon_sentiment_analysis()
    {
        // Seed words
        PositiveWord::create(['word' => 'increase']);
        PositiveWord::create(['word' => 'growth']);
        NegativeWord::create(['word' => 'inflation']);
        NegativeWord::create(['word' => 'war']);
        NegativeWord::create(['word' => 'decrease']);

        // Clear cache
        Cache::forget('lexicon_positive_words');
        Cache::forget('lexicon_negative_words');

        $service = new SentimentService();

        // 1. Negative sentiment case
        $result = $service->analyze("increase growth inflation war decrease");
        
        $this->assertEquals(2, $result['positive_score']);
        $this->assertEquals(3, $result['negative_score']);
        $this->assertEquals('negative', $result['label']);

        // 2. Positive sentiment case
        $result2 = $service->analyze("increase growth");
        $this->assertEquals(2, $result2['positive_score']);
        $this->assertEquals(0, $result2['negative_score']);
        $this->assertEquals('positive', $result2['label']);

        // 3. Neutral sentiment case
        $result3 = $service->analyze("growth war");
        $this->assertEquals(1, $result3['positive_score']);
        $this->assertEquals(1, $result3['negative_score']);
        $this->assertEquals('neutral', $result3['label']);
    }
}
