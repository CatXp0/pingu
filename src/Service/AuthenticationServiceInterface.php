<?php

declare(strict_types=1);

namespace App\Service;

interface AuthenticationServiceInterface
{
    public function getToken(): string;
}
