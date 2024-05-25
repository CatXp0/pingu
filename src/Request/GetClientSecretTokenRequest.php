<?php

declare(strict_types=1);

namespace App\Request;

use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;

class GetClientSecretTokenRequest extends Request
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly string $endpoint)
    {
        parent::__construct(
            'POST',
            $this->endpoint,
        );
    }
}
