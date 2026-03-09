<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\IpGeolocationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class IpGeolocationServiceTest extends TestCase
{
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetCountryReturnsCountryOnSuccess(): void
    {
        $mockResponse = new MockResponse(
            json_encode(['country' => 'United States']),
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $service = new IpGeolocationService($httpClient, $this->logger);

        $this->assertSame('United States', $service->getCountry('8.8.8.8'));
    }

    public function testGetCountryReturnsNullAndLogsWarningOnHttpError(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('failed for IP'),
                $this->callback(fn (array $context) => $context['ip'] === 'invalid'),
            );

        $service = new IpGeolocationService($httpClient, $this->logger);

        $this->assertNull($service->getCountry('invalid'));
    }

    public function testGetCountryReturnsNullAndLogsWarningOnInvalidJson(): void
    {
        $mockResponse = new MockResponse(
            'not json',
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $this->logger->expects($this->once())
            ->method('warning');

        $service = new IpGeolocationService($httpClient, $this->logger);

        $this->assertNull($service->getCountry('8.8.8.8'));
    }

    public function testGetCountryReturnsNullAndLogsNoticeWhenCountryFieldMissing(): void
    {
        $mockResponse = new MockResponse(
            json_encode(['city' => 'Mountain View']),
            ['http_code' => 200],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with(
                $this->stringContains('missing "country" field'),
                $this->callback(fn (array $context) => $context['ip'] === '8.8.8.8'),
            );

        $service = new IpGeolocationService($httpClient, $this->logger);

        $this->assertNull($service->getCountry('8.8.8.8'));
    }
}
