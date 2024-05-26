<?php

declare(strict_types=1);

namespace App\Message;

final class DeleteAffectiveItemsAnalysisRating implements ModifyAffectiveItemsAnalysisRatingInterface
{
    public function __construct(
        private readonly ?int $reviewId,
        private readonly int $productId,
        private readonly ?string $reviewContent,
    ) {
    }

    public function getReviewContent(): ?string
    {
        return $this->reviewContent;
    }

    public function getReviewId(): ?int
    {
        return $this->reviewId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
