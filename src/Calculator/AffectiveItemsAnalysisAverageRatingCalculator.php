<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Product\ProductReview;
use Sylius\Component\Review\Calculator\ReviewableRatingCalculatorInterface;
use Sylius\Component\Review\Model\ReviewableInterface;
use Sylius\Component\Review\Model\ReviewInterface;

class AffectiveItemsAnalysisAverageRatingCalculator implements ReviewableRatingCalculatorInterface
{
    /**
     * Calculeaza media scorurilor itemilor afectivi
     */
    public function calculate(ReviewableInterface $reviewable): float
    {
        $sum = 0;
        $reviewsNumber = 0;
        $reviews = $reviewable->getReviews();

        /** @var ProductReview $review */
        foreach ($reviews as $review) {
            // daca feedback-ul nu are statusul accepted, nu il punem la socoteala
            if (ReviewInterface::STATUS_ACCEPTED !== $review->getStatus()) {
                continue;
            }
            // daca nu avem un rating pe feedback, nu il punem la socoteala
            if (is_null($review->getAffectiveItemsAnalysisRating())) {
                continue;
            }

            ++$reviewsNumber;
            $sum += $review->getAffectiveItemsAnalysisRating();
        }

        // intoarcem media aritmetica daca avem feedbackuri, 0 daca nu
        return 0 !== $reviewsNumber ? $sum / $reviewsNumber : 0;
    }
}
