<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class IpGeolocationService implements IpGeolocationServiceInterface
{
    private const string API_URL = 'https://www.iplocate.io/api/lookup/%s';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function getCountry(string $ip): ?string
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf(self::API_URL, $ip),
            );

            $data = $response->toArray();

            return $data['country'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}
