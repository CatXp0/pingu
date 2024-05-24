<?php

declare(strict_types=1);

namespace App\Message;

final class DeleteAffectiveItemsAnalysisRating implements ModifyAffectiveItemsAnalysisRatingInterface
{
    public function __construct(private ?int $reviewId, private int $productId, private ?string $reviewContent)
    {
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
