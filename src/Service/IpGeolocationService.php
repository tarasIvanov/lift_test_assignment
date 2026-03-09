<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class IpGeolocationService implements IpGeolocationServiceInterface
{
    private const string API_URL = 'https://www.iplocate.io/api/lookup/%s';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
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

            if (!isset($data['country'])) {
                $this->logger->notice('Geolocation API response missing "country" field for IP {ip}', [
                    'ip' => $ip,
                ]);

                return null;
            }

            return $data['country'];
        } catch (\Throwable $e) {
            $this->logger->warning('Geolocation API request failed for IP {ip}: {error}', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
