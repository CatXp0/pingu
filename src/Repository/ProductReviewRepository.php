<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductReviewRepository as BaseProductReviewRepository;
use Sylius\Component\Core\Dashboard\Interval;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Review\Model\ReviewInterface;

final class ProductReviewRepository extends BaseProductReviewRepository
{
    public function getProductReviewsSummary(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        Interval $interval,
        int $productId,
    ): array {
        $queryBuilder = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->select('AVG(o.affectiveItemsAnalysisRating) AS averageRating')
            ->andWhere('o.reviewSubject = :productId')
            ->andWhere('o.status = :status')
            ->andWhere(':channel MEMBER OF product.channels')
            ->setParameter('productId', $productId)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('channel', $channel);

        switch ($interval->asString()) {
            case 'year':
                $queryBuilder
                    ->addSelect('YEAR(o.createdAt) as year')
                    ->groupBy('year')
                    ->andWhere('YEAR(o.createdAt) >= :startYear AND YEAR(o.createdAt) <= :endYear')
                    ->setParameter('startYear', $startDate->format('Y'))
                    ->setParameter('endYear', $endDate->format('Y'));
                $dateFormatter = static function (\DateTimeInterface $date): string {
                    return $date->format('Y');
                };
                $resultFormatter = static function (array $data): string {
                    return (string)$data['year'];
                };

                break;
            case 'month':
                $queryBuilder
                    ->addSelect('YEAR(o.createdAt) as year')
                    ->addSelect('MONTH(o.createdAt) as month')
                    ->groupBy('year')
                    ->addGroupBy('month')
                    ->andWhere($queryBuilder->expr()->orX(
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND MONTH(o.createdAt) >= :startMonth AND MONTH(o.createdAt) <= :endMonth',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) != :endYear AND MONTH(o.createdAt) >= :startMonth',
                        'YEAR(o.createdAt) = :endYear AND YEAR(o.createdAt) != :startYear AND MONTH(o.createdAt) <= :endMonth',
                        'YEAR(o.createdAt) > :startYear AND YEAR(o.createdAt) < :endYear',
                    ))
                    ->setParameter('startYear', $startDate->format('Y'))
                    ->setParameter('startMonth', $startDate->format('n'))
                    ->setParameter('endYear', $endDate->format('Y'))
                    ->setParameter('endMonth', $endDate->format('n'));

                $dateFormatter = static function (\DateTimeInterface $date): string {
                    return $date->format('n.Y');
                };
                $resultFormatter = static function (array $data): string {
                    return $data['month'] . '.' . $data['year'];
                };
//
                break;
            case 'week':
                $queryBuilder
                    ->addSelect('YEAR(o.createdAt) as year')
                    ->addSelect('WEEK(o.createdAt) as week')
                    ->groupBy('year')
                    ->addGroupBy('week')
                    ->andWhere($queryBuilder->expr()->orX(
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND WEEK(o.createdAt) >= :startWeek AND WEEK(o.createdAt) <= :endWeek',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) != :endYear AND WEEK(o.createdAt) >= :startWeek',
                        'YEAR(o.createdAt) = :endYear AND YEAR(o.createdAt) != :startYear AND WEEK(o.createdAt) <= :endWeek',
                        'YEAR(o.createdAt) > :startYear AND YEAR(o.createdAt) < :endYear',
                    ))
                    ->setParameter('startYear', $startDate->format('Y'))
                    ->setParameter('startWeek', (ltrim($startDate->format('W'), '0') ?: '0'))
                    ->setParameter('endYear', $endDate->format('Y'))
                    ->setParameter('endWeek', (ltrim($endDate->format('W'), '0') ?: '0'))
                ;
                $dateFormatter = static function (\DateTimeInterface $date): string {
                    return (ltrim($date->format('W'), '0') ?: '0') . ' ' . $date->format('Y');
                };
                $resultFormatter = static function (array $data): string {
                    return $data['week'] . ' ' . $data['year'];
                };

                break;
            case 'day':
                $queryBuilder
                    ->addSelect('YEAR(o.createdAt) as year')
                    ->addSelect('MONTH(o.createdAt) as month')
                    ->addSelect('DAY(o.createdAt) as day')
                    ->groupBy('year')
                    ->addGroupBy('month')
                    ->addGroupBy('day')
                    ->andWhere($queryBuilder->expr()->orX(
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND MONTH(o.createdAt) = :startMonth AND MONTH(o.createdAt) = :endMonth AND DAY(o.createdAt) >= :startDay AND DAY(o.createdAt) <= :endDay',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND MONTH(o.createdAt) = :startMonth AND MONTH(o.createdAt) != :endMonth AND DAY(o.createdAt) >= :startDay',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND MONTH(o.createdAt) = :endMonth AND MONTH(o.createdAt) != :startMonth AND DAY(o.createdAt) <= :endDay',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) = :endYear AND MONTH(o.createdAt) > :startMonth AND MONTH(o.createdAt) < :endMonth',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) != :endYear AND MONTH(o.createdAt) = :startMonth AND DAY(o.createdAt) >= :startDay',
                        'YEAR(o.createdAt) = :startYear AND YEAR(o.createdAt) != :endYear AND MONTH(o.createdAt) > :startMonth',
                        'YEAR(o.createdAt) = :endYear AND YEAR(o.createdAt) != :startYear AND MONTH(o.createdAt) = :endMonth AND DAY(o.createdAt) <= :endDay',
                        'YEAR(o.createdAt) = :endYear AND YEAR(o.createdAt) != :startYear AND MONTH(o.createdAt) < :endMonth',
                        'YEAR(o.createdAt) > :startYear AND YEAR(o.createdAt) < :endYear',
                    ))
                    ->setParameter('startYear', $startDate->format('Y'))
                    ->setParameter('startMonth', $startDate->format('n'))
                    ->setParameter('startDay', $startDate->format('j'))
                    ->setParameter('endYear', $endDate->format('Y'))
                    ->setParameter('endMonth', $endDate->format('n'))
                    ->setParameter('endDay', $endDate->format('j'))
                ;
                $dateFormatter = static function (\DateTimeInterface $date): string {
                    return $date->format('j.n.Y');
                };
                $resultFormatter = static function (array $data): string {
                    return $data['day'] . '.' . $data['month'] . '.' . $data['year'];
                };

                break;
            default:
                throw new \RuntimeException(sprintf('Interval "%s" not supported.', $interval->asString()));
        }

        $reviewsAverages = $queryBuilder->getQuery()->getArrayResult();
        $salesData = [];

        $period = new \DatePeriod($startDate, \DateInterval::createFromDateString(sprintf('1 %s', $interval->asString())), $endDate);
        foreach ($period as $date) {
            $salesData[$dateFormatter($date)] = ['y' => 0, 'x' => $dateFormatter($date)];
        }

        foreach ($reviewsAverages as $item) {
            $salesData[$resultFormatter($item)]['y'] = (float) $item['averageRating'];
        }

        return $salesData;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getTotalRatingAverageForProductInPeriod(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $productId,
    ): float {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->select('AVG(o.affectiveItemsAnalysisRating) AS averageRating')
            ->where('o.reviewSubject = :productId')
            ->andWhere('o.status = :status')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->setParameter('productId', $productId)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('channel', $channel)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function getProductReviewsFromInterval(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $productId,
    ): array {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->where('o.reviewSubject = :productId')
            ->andWhere('o.status = :status')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->setParameter('productId', $productId)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('channel', $channel)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    public function getPositiveReviewsForProductInPeriod(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $productId,
    ): int {
        $queryBuilder = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product');

        return $queryBuilder->select($queryBuilder->expr()->count('o.id') . ' AS itemsNumber')
            ->addSelect('ROUND(o.affectiveItemsAnalysisRating) AS roundedRating')
            ->where('o.reviewSubject = :productId')
            ->andWhere('o.status = :status')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->andWhere('ROUND(o.affectiveItemsAnalysisRating) >= 4')
            ->andWhere('ROUND(o.affectiveItemsAnalysisRating) <= 5')
            ->setParameter('productId', $productId)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('channel', $channel)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('roundedRating')
            ->orderBy('itemsNumber', 'desc')
            ->addOrderBy('roundedRating', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult()[0]['itemsNumber'] ?? 0;
    }

    public function getModeSentimentScoreForProductInPeriod(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $productId,
    ): int {
        $queryBuilder = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product');

        return $queryBuilder->select($queryBuilder->expr()->count('o.id') . ' AS itemsNumber')
            ->addSelect('ROUND(o.affectiveItemsAnalysisRating) AS roundedRating')
            ->andWhere('o.reviewSubject = :productId')
            ->andWhere('o.status = :status')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->setParameter('productId', $productId)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('channel', $channel)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('roundedRating')
            ->orderBy('itemsNumber', 'desc')
            ->addOrderBy('roundedRating', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult()[0]['roundedRating'] ?? 0;
    }
}
