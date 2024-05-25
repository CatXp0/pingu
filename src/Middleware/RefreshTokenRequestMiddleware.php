<?php

namespace App\Middleware;

use App\Service\AuthenticationServiceInterface;
use Psr\Http\Message\RequestInterface;

class RefreshTokenRequestMiddleware
{
    public function __construct(private readonly AuthenticationServiceInterface $authenticationService)
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): mixed {
            // pentru fiecare request, se adauga un token pentru autorizare
            $token = $this->authenticationService->getToken();
            // tokenul se adauga in header
            $request = $request->withHeader('Authorization', "Bearer $token");

            return $handler($request, $options);
        };
    }
}
