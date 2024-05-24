<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\ApiRequestFailedException;
use App\Middleware\RefreshTokenRequestMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PinguApiClient
{
    private Client $client;

    public function __construct(private RefreshTokenRequestMiddleware $refreshTokenRequestMiddleware)
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push($this->refreshTokenRequestMiddleware);

        $options = [
            'handler' => $handlerStack,
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            RequestOptions::TIMEOUT => 30,
            RequestOptions::CONNECT_TIMEOUT => 30,
            RequestOptions::VERIFY => false
        ];

        $this->client = new Client($options);
    }

    /**
     * @throws ApiRequestFailedException
     */
    public function request(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            return $this->client->send($request, $options);
        } catch (Throwable $exception) {
            throw (new ApiRequestFailedException(
                message: $exception->getMessage() . ' ' . $request->getBody(),
                code: $exception->getCode(),
                previous: $exception,
            ));
        }
    }
}
