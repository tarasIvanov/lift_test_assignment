<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateContactControllerTest extends WebTestCase
{
    public function testPostValidContactReturns202(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumbers' => ['+1234567890'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
    }

    public function testPostMissingFirstNameReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'lastName' => 'Doe',
            'phoneNumbers' => ['+1234567890'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostMissingLastNameReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'John',
            'phoneNumbers' => ['+1234567890'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostEmptyPhoneNumbersReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumbers' => [],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostPhoneNumbersWithEmptyStringReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumbers' => [''],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostInvalidJsonReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contacts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'not json');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
