<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\TokenRetrievalException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PinguApiAuthenticationClient
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @throws TokenRetrievalException
     */
    public function request(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            return $this->client->send($request, $options);
        } catch (ConnectException $exception) {
            throw new TokenRetrievalException(
                message: $exception->getMessage(),
                code: 0,
                previous: $exception
            );
        } catch (Throwable $exception) {
            throw new TokenRetrievalException(
                message: $exception->getMessage(),
                code: (int)$exception->getCode(),
                previous: $exception,
            );
        }
    }
}
