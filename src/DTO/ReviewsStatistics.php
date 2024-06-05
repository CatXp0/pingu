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

namespace App\DTO;

use App\Entity\Product\ProductReview;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Review\Model\ReviewInterface;

class ReviewsStatistics
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly array $productReviews,
        private readonly array $allProductReviews,
        private readonly float $ratingAverage,
        private readonly int $positiveReviews,
        private readonly int $modeSentimentScore,
        private readonly ?ChannelInterface $channel = null,
    ) {
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function getRatingAverage(): float
    {
        return (float)number_format($this->ratingAverage, 2, '.', '');
    }

    public function getStandardDeviation(): float
    {
        $scores = [];
        $sum = 0;
        $count = 0;

        // calculam suma scorurilor, le punem intr-un array, le numaram
        /** @var ProductReview $productReview */
        foreach ($this->productReviews as $productReview) {
            if ($productReview->getStatus() !== ReviewInterface::STATUS_ACCEPTED) {
                continue;
            }

            $scores[] = $productReview->getAffectiveItemsAnalysisRating();
            $sum += $productReview->getAffectiveItemsAnalysisRating();
            $count++;
        }

        if ($count === 0) {
            return 0;
        }
        // calculam media
        $mean = $sum / $count;
        // calculam varianta
        $variance = 0;
        foreach ($scores as $score) {
            $variance += pow(($score - $mean), 2);
        }
        $variance /= $count;

        // abaterea standard este radacina patraata a variantei
        return (float)number_format(sqrt($variance), 2, '.', '');
    }

    public function getPercentageOfPositiveReviews(): float
    {
        if (count($this->productReviews) === 0) {
            return 0;
        }

        return (float)number_format($this->positiveReviews * 100 / count($this->productReviews), 2, '.', '');
    }

    public function getModeSentimentScore(): int
    {
        return $this->modeSentimentScore;
    }

    public function getTotalProductAverageRating(): float
    {
        if (count($this->allProductReviews) === 0) {
            return 0;
        }

        $userScores = 0;

        /** @var ProductReview $productReview */
        foreach ($this->allProductReviews as $productReview) {
            if ($productReview->getStatus() !== ReviewInterface::STATUS_ACCEPTED) {
                continue;
            }

            $userScores += $productReview->getRating();
        }

        return (float)number_format($userScores / count($this->allProductReviews), 2, '.', '');
    }

    public function getTotalProductAverageAnalysisRating(): float
    {
        if (count($this->allProductReviews) === 0) {
            return 0;
        }

        $affectiveItemsScores = 0;

        /** @var ProductReview $productReview */
        foreach ($this->allProductReviews as $productReview) {
            if ($productReview->getStatus() !== ReviewInterface::STATUS_ACCEPTED) {
                continue;
            }

            $affectiveItemsScores += $productReview->getAffectiveItemsAnalysisRating();
        }

        return (float)number_format($affectiveItemsScores/count($this->allProductReviews), 2, '.', '');
    }

    public function getPearsonCorrelationCoefficient(): float
    {
        $userScores = [];
        $affectiveItemsScores = [];

        /** @var ProductReview $productReview */
        foreach ($this->productReviews as $productReview) {
            if ($productReview->getStatus() !== ReviewInterface::STATUS_ACCEPTED) {
                continue;
            }

            $userScores[] = $productReview->getRating();
            $affectiveItemsScores[] = $productReview->getAffectiveItemsAnalysisRating();
        }

        return (float)number_format($this->calculatePearsonCoefficient($userScores, $affectiveItemsScores), 2, '.', '');
    }

    /**
     * Calculeaza scorul coeficientului pearson pentru doua seturi de date
     */
    private function calculatePearsonCoefficient(array $a, array $b): float {
        $length = count($a);

        if ($length === 0) {
            return 0;
        }

        // suma tuturor elementelor pentru ambele seturi de date
        $sumX = array_sum($a);
        $sumY = array_sum($b);

        // suma patratelor fiecărui element din ambele seturi
        $sumXSq = array_sum(array_map(function($item) { return pow($item, 2); }, $a));
        $sumYSq = array_sum(array_map(function($item) { return pow($item, 2); }, $b));

        // suma produselor corespunzatoare ale elementelor din cele doua seturi
        $productSum = 0;
        for ($i = 0; $i < $length; $i++) {
            $productSum += $a[$i] * $b[$i];
        }

        // formulele matematice pentru coeficientul Pearson pentru a determina numaratorul si numitorul
        $numerator = $productSum - (($sumX * $sumY) / $length);
        $denominator = sqrt(
            ($sumXSq - pow($sumX, 2) / $length) * ($sumYSq - pow($sumY, 2) / $length)
        );
        // pentru a evita impartirea la 0
        if ($denominator == 0) {
            return 0;
        }

        return $numerator / $denominator;
    }
}
