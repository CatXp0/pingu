<?php

declare(strict_types=1);

namespace App\DTO;

class RatingProbability
{
    public function __construct(
        public float $one,
        public float $two,
        public float $three,
        public float $four,
        public float $five,
    ) {
    }

    public function getProbabilityRating(): float
    {
        return 1 * $this->one + 2 * $this->two + 3 * $this->three + 4 * $this->four + 5 * $this->five;
    }
}
