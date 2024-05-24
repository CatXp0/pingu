<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\TokenRetrievalException;
use App\Client\PinguApiAuthenticationClient;
use App\Request\GetClientSecretTokenRequest;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class AuthenticationService implements AuthenticationServiceInterface
{
    private const JWT_KEY = "auth.jwt_token";

    private FilesystemAdapter $cache;

    public function __construct(
        private PinguApiAuthenticationClient $apiClient,
        private LoggerInterface $logger,
        private ParameterBagInterface $params,
    ) {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @throws TokenRetrievalException
     * @throws InvalidArgumentException
     */
    public function getToken(): string
    {
        try {
            $token = $this->getCachedToken();
        } catch (Throwable $exception) {
            $this->logger->warning(
                'AuthenticationService: Error occurred, generating new token',
                [
                    'message' => $exception->getMessage(),
                    'exception' => $exception,
                ],
            );

            $token = null;
        }

        if ($token !== null) {
            return $token;
        }

        $token = $this->requestNewClientSecretToken($this->params->get('pingu_api.authentication.payload') ?? []);

        $cacheItem = $this->cache->getItem(self::JWT_KEY);
        $cacheItem->set($token);
        $cacheItem->expiresAfter(1740);

        $this->cache->save($cacheItem);

        return $token;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getCachedToken(): ?string
    {
        $cacheItem = $this->cache->getItem(self::JWT_KEY);
        if (!$cacheItem->isHit()) {
            return null;
        }

        return $cacheItem->get();
    }

    /**
     * @throws TokenRetrievalException
     */
    public function requestNewClientSecretToken(array $payload): string
    {
        $this->logger->info('Requesting new token');

        $formParams = [];
        foreach ($payload as $name => $value) {
            $formParams[] = [
                'name' => $name,
                'contents' => $value,
            ];
        }

        $request = new GetClientSecretTokenRequest($this->params->get('pingu_api.authentication.endpoint'));

        $response = $this->apiClient->request($request, ['multipart' => $formParams]);

        $payload = json_decode($response->getBody()->getContents());

        if (empty($payload?->access_token ?? null)) {
            throw new TokenRetrievalException('Could not retrieve token from payload');
        }

        $this->logger->info('New token was received');

        return $payload->access_token;
    }
}
