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
        private readonly PinguApiAuthenticationClient $apiClient,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $params,
    ) {
        // initializam cache-ul
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Obtine un token nou sau din cache
     *
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

        // daca gasim un token in cache, il returnam
        if ($token !== null) {
            return $token;
        }

        // solicitam un token nou
        $token = $this->requestNewClientSecretToken($this->params->get('pingu_api.authentication.payload') ?? []);

        // salvam token-ul in cache, cu o cheie
        $cacheItem = $this->cache->getItem(self::JWT_KEY);
        $cacheItem->set($token);
        // il punem sa expire in 1740 de secunde
        $cacheItem->expiresAfter(1740);

        $this->cache->save($cacheItem);

        return $token;
    }

    /**
     * Cauta token-ul in cache dupa cheie
     *
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
     * Obtine un token nou de la API
     *
     * @throws TokenRetrievalException
     */
    public function requestNewClientSecretToken(array $payload): string
    {
        $this->logger->info('Requesting new token');

        // initializam parametrii pentru request
        $formParams = [];
        foreach ($payload as $name => $value) {
            $formParams[] = [
                'name' => $name,
                'contents' => $value,
            ];
        }

        // initializam request-ul
        $request = new GetClientSecretTokenRequest($this->params->get('pingu_api.authentication.endpoint'));
        // sa face request-ul catre API
        $response = $this->apiClient->request($request, ['multipart' => $formParams]);
        // decodam raspunsul json
        $payload = json_decode($response->getBody()->getContents());

        if (empty($payload?->access_token ?? null)) {
            throw new TokenRetrievalException('Could not retrieve token from payload');
        }

        $this->logger->info('New token was received');
        // returnam token-ul
        return $payload->access_token;
    }
}
