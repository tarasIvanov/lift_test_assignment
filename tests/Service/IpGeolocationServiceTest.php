<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\IpGeolocationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class IpGeolocationServiceTest extends TestCase
{
    public function testGetCountryReturnsCountryOnSuccess(): void
    {
        $mockResponse = new MockResponse(
            json_encode(['country' => 'United States']),
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $service = new IpGeolocationService($httpClient);

        $this->assertSame('United States', $service->getCountry('8.8.8.8'));
    }

    public function testGetCountryReturnsNullOnHttpError(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);

        $service = new IpGeolocationService($httpClient);

        $this->assertNull($service->getCountry('invalid'));
    }

    public function testGetCountryReturnsNullOnInvalidJson(): void
    {
        $mockResponse = new MockResponse(
            'not json',
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $service = new IpGeolocationService($httpClient);

        $this->assertNull($service->getCountry('8.8.8.8'));
    }

    public function testGetCountryReturnsNullWhenCountryFieldMissing(): void
    {
        $mockResponse = new MockResponse(
            json_encode(['city' => 'Mountain View']),
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $service = new IpGeolocationService($httpClient);

        $this->assertNull($service->getCountry('8.8.8.8'));
    }
}
