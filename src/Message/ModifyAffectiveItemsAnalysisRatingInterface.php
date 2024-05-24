<?php

declare(strict_types=1);

namespace App\Message;

interface ModifyAffectiveItemsAnalysisRatingInterface
{
    public function getReviewContent(): ?string;

    public function getReviewId(): ?int;

    public function getProductId(): int;
}
