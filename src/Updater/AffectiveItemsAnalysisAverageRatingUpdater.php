<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Updater;

use App\Calculator\AffectiveItemsAnalysisAverageRatingCalculator;
use App\Entity\Product\Product;
use App\Entity\Product\ProductReview;
use Doctrine\ORM\EntityManagerInterface;

class AffectiveItemsAnalysisAverageRatingUpdater
{
    public function __construct(
        private AffectiveItemsAnalysisAverageRatingCalculator $averageRatingCalculator,
        private EntityManagerInterface $reviewSubjectManager,
    ) {
    }

    public function update(Product $reviewSubject): void
    {
        $this->modifyReviewSubjectAffectiveItemsAnalysisAverageRating($reviewSubject);
    }

    public function updateFromReview(ProductReview $review): void
    {
        $this->modifyReviewSubjectAffectiveItemsAnalysisAverageRating($review->getReviewSubject());
    }

    private function modifyReviewSubjectAffectiveItemsAnalysisAverageRating(Product $product): void
    {
        $affectiveItemsAnalysisAverageRating = $this->averageRatingCalculator->calculate($product);

        if (0.0 === $affectiveItemsAnalysisAverageRating) {
            return;
        }

        $product->setAffectiveItemsAnalysisAverageRating($affectiveItemsAnalysisAverageRating);

        $this->reviewSubjectManager->flush();
    }
}
