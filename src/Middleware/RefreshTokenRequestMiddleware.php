<?php

namespace App\Middleware;

use App\Service\AuthenticationServiceInterface;
use Psr\Http\Message\RequestInterface;

class RefreshTokenRequestMiddleware
{
    public function __construct(private AuthenticationServiceInterface $authenticationService)
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): mixed {
            $token = $this->authenticationService->getToken();
            $request = $request->withHeader('Authorization', "Bearer $token");

            return $handler($request, $options);
        };
    }
}
