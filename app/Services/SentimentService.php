<?php

namespace App\Services;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Support\Facades\Cache;

class SentimentService
{
    /**
     * Analyze the sentiment of a given text (title + description)
     * using lexicon matching against positive_words and negative_words tables.
     */
    public function analyze(string $title, ?string $description = ''): array
    {
        $text = $title . ' ' . ($description ?? '');
        
        // Tokenize: remove punctuation and lowercase
        $cleanText = strtolower(preg_replace('/[^\w\s]/u', ' ', $text));
        $words = preg_split('/\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

        // Fetch lexicon lists, cached for 1 hour to ensure high performance
        $positiveWords = Cache::remember('lexicon_positive_words', 3600, function () {
            return PositiveWord::pluck('word')->toArray();
        });

        $negativeWords = Cache::remember('lexicon_negative_words', 3600, function () {
            return NegativeWord::pluck('word')->toArray();
        });

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $positiveScore++;
            }
            if (in_array($word, $negativeWords)) {
                $negativeScore++;
            }
        }

        $label = 'neutral';
        if ($positiveScore > $negativeScore) {
            $label = 'positive';
        } elseif ($negativeScore > $positiveScore) {
            $label = 'negative';
        }

        return [
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'label' => $label
        ];
    }
}
