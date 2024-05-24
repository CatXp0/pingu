<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Product\ProductReview;
use Sylius\Component\Review\Calculator\ReviewableRatingCalculatorInterface;
use Sylius\Component\Review\Model\ReviewableInterface;
use Sylius\Component\Review\Model\ReviewInterface;

class AffectiveItemsAnalysisAverageRatingCalculator implements ReviewableRatingCalculatorInterface
{
    public function calculate(ReviewableInterface $reviewable): float
    {
        $sum = 0;
        $reviewsNumber = 0;
        $reviews = $reviewable->getReviews();

        /** @var ProductReview $review */
        foreach ($reviews as $review) {
            if (ReviewInterface::STATUS_ACCEPTED !== $review->getStatus()) {
                continue;
            }

            if (is_null($review->getAffectiveItemsAnalysisRating())) {
                continue;
            }

            ++$reviewsNumber;
            $sum += $review->getAffectiveItemsAnalysisRating();
        }

        return 0 !== $reviewsNumber ? $sum / $reviewsNumber : 0;
    }
}
