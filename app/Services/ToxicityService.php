<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Football-Specific Toxicity Analysis Service
 * 
 * Analyzes text content for toxic language using a weighted lexicon
 * of football-specific abuse terms and returns a toxicity score.
 */
class ToxicityService
{
    /**
     * Football-specific weighted lexicon
     * Key: term, Value: weight (severity multiplier)
     */
    private const LEXICON = [
        'kill' => 1.0,
        'murder' => 1.0,
        'stab' => 1.0,
        'knife' => 0.9,
        'hurt' => 0.5,
        'attack' => 0.7,
        'violence' => 0.8,
        'violent' => 0.8,
        'racist' => 1.0,
        'racism' => 1.0,
        'nigger' => 1.0,
        'paki' => 1.0,
        'chink' => 1.0,
        'yid' => 0.9,
        'hooligan' => 0.6,
        'hooligans' => 0.6,
        'scum' => 0.7,
        'trash' => 0.5,
        'filth' => 0.6,
        'hate' => 0.6,
        'fucking' => 0.4,
        'fuck' => 0.4,
        'shit' => 0.3,
        'bastard' => 0.5,
        'cunt' => 0.7,
        'wanker' => 0.4,
        'dickhead' => 0.4,
        'idiot' => 0.3,
        'moron' => 0.3,
        'stupid' => 0.2,
        'coward' => 0.4,
        'disgrace' => 0.4,
        'pathetic' => 0.3,
        'useless' => 0.3,
        'die' => 0.8,
        'death' => 0.7,
        'threat' => 0.8,
        'threaten' => 0.8,
    ];

    /**
     * Analyze text and return toxicity score
     * 
     * @param string $text Text to analyze
     * @return float Score between 0.0 and 1.0
     */
    public function analyse(string $text): float
    {
        if (empty(trim($text))) {
            return 0.0;
        }

        // Normalize text: lowercase, remove special chars except spaces
        $normalized = strtolower($text);
        $normalized = preg_replace('/[^a-z0-9\s]/', ' ', $normalized);
        
        // Tokenize
        $tokens = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($tokens)) {
            return 0.0;
        }

        $totalWeight = 0.0;
        $matchCount = 0;

        // Check each token against lexicon
        foreach ($tokens as $token) {
            if (isset(self::LEXICON[$token])) {
                $totalWeight += self::LEXICON[$token];
                $matchCount++;
            }
        }

        // Calculate density score: (total weight / token count)
        // This gives us a measure of toxic content relative to overall content
        $densityScore = $totalWeight / count($tokens);

        // Clamp to [0.0, 1.0]
        $score = min(1.0, max(0.0, $densityScore));

        return round($score, 4);
    }

    /**
     * Check if a toxicity score exceeds the threshold
     * 
     * @param float $score Toxicity score
     * @return bool True if toxic
     */
    public function isToxic(float $score): bool
    {
        $threshold = (float)($_ENV['TOXICITY_THRESHOLD'] ?? 0.6);
        return $score >= $threshold;
    }

    /**
     * Get the toxicity threshold from environment
     * 
     * @return float Threshold value
     */
    public function getThreshold(): float
    {
        return (float)($_ENV['TOXICITY_THRESHOLD'] ?? 0.6);
    }
}
