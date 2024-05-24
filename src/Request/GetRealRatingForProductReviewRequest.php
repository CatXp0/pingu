<?php

declare(strict_types=1);

namespace App\Request;

use GuzzleHttp\Psr7\Request;

class GetRealRatingForProductReviewRequest extends Request
{
    public function __construct(private string $review, private string $endpoint)
    {
        parent::__construct(
            'POST',
            $this->endpoint,
            [],
            json_encode(['text' => $this->review]),
        );
    }
}
