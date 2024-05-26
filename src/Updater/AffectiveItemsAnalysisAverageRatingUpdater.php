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
use Psr\Log\LoggerInterface;

class AffectiveItemsAnalysisAverageRatingUpdater
{
    public function __construct(
        private readonly AffectiveItemsAnalysisAverageRatingCalculator $averageRatingCalculator,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function update(Product $reviewSubject): void
    {
        $this->modifyReviewSubjectAffectiveItemsAnalysisAverageRating($reviewSubject);
    }

    /**
     * Updatam media scorului pentru un feedback
     */
    public function updateFromReview(ProductReview $review): void
    {
        $this->modifyReviewSubjectAffectiveItemsAnalysisAverageRating($review->getReviewSubject());
    }

    private function modifyReviewSubjectAffectiveItemsAnalysisAverageRating(Product $product): void
    {
        $affectiveItemsAnalysisAverageRating = $this->averageRatingCalculator->calculate($product);
        // daca scorul este 0 (adica nu avem niciun feedback), nu il punem
        if (0.0 === $affectiveItemsAnalysisAverageRating) {
            return;
        }
        // setam proprietatea mediei analizei pe produs
        $product->setAffectiveItemsAnalysisAverageRating($affectiveItemsAnalysisAverageRating);
        // flush la schimbari in db
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
