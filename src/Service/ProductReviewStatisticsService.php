<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ReviewsStatistics;
use App\Repository\ProductReviewRepository;
use Sylius\Component\Core\Dashboard\Interval;
use Sylius\Component\Core\Model\ChannelInterface;

class ProductReviewStatisticsService
{
    public function __construct(
        private readonly ProductReviewRepository $productReviewRepository,
    ) {
    }

    public function getRawData(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $interval,
        int $productId,
    ): array {
        $statistics = $this->getStatisticsForChannelInPeriod($channel, $startDate, $endDate, $productId);

        $summary = $this->productReviewRepository->getProductReviewsSummary(
            $channel,
            $startDate,
            $endDate,
            Interval::{$interval}(),
            $productId,
        );

        /** @var string $currencyCode */
        $currencyCode = $channel->getBaseCurrency()->getCode();

        return [
            'product_id' => $productId,
            'sales_summary' => [
                'intervals' => array_keys($summary),
                'sales' => array_values($summary),
            ],
            'channel' => [
                'base_currency_code' => $currencyCode,
                'channel_code' => $channel->getCode(),
            ],
            'statistics' => [
                'average' => $statistics->getRatingAverage(),
                'total_average_user_rating' => $statistics->getTotalProductAverageRating(),
                'total_average_ai_rating' => $statistics->getTotalProductAverageAnalysisRating(),
                'pearson_correlation_coefficient' => $statistics->getPearsonCorrelationCoefficient(),
                'standard_deviation' => $statistics->getStandardDeviation(),
                'percentage_of_positive_reviews' => $statistics->getPercentageOfPositiveReviews(),
                'mode_sentiment_score' => $statistics->getModeSentimentScore(),
            ],
        ];
    }

    private function getStatisticsForChannelInPeriod(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $productId,
    ): ReviewsStatistics {
        $productReviewsFromInterval = $this->productReviewRepository->getProductReviewsFromInterval(
            $channel,
            $startDate,
            $endDate,
            $productId,
        );

        $allProductReviews = $this->productReviewRepository->findBy(['reviewSubject' => $productId]);

        return new ReviewsStatistics(
            $productReviewsFromInterval,
            $allProductReviews,
            $this->productReviewRepository->getTotalRatingAverageForProductInPeriod(
                $channel,
                $startDate,
                $endDate,
                $productId,
            ),
            $this->productReviewRepository->getPositiveReviewsForProductInPeriod(
                $channel,
                $startDate,
                $endDate,
                $productId,
            ),
            $this->productReviewRepository->getModeSentimentScoreForProductInPeriod(
                $channel,
                $startDate,
                $endDate,
                $productId,
            ),
        );
    }
}
