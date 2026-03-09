<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListenerTest extends WebTestCase
{
    public function testNotFoundRouteReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/nonexistent');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('code', $data);
        $this->assertSame(404, $data['code']);
    }

    public function testValidationErrorReturns400(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/contacts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['firstName' => '', 'lastName' => '', 'phoneNumbers' => []]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }
}
