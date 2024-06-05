<?php

declare(strict_types=1);

namespace App\DTO;

use Sylius\Component\Core\Dashboard\SalesSummaryInterface;

/**
 * @experimental
 */
final class ReviewsSummary implements SalesSummaryInterface
{
    public function __construct(
        /** @var array<string, string> */
        private readonly array $intervalsSalesMap,
    ) {
    }

    public function getIntervals(): array
    {
        return array_keys($this->intervalsSalesMap);
    }

    public function getSales(): array
    {
        return array_values($this->intervalsSalesMap);
    }
}
