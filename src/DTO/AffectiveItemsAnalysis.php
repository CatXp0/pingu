<?php

declare(strict_types=1);

namespace App\DTO;

class AffectiveItemsAnalysis
{
    public function __construct(
        public int $affectiveItemsAnalysisRating,
        public float $confidence,
        public RatingProbability $ratingProbability,
    ) {
    }
}
