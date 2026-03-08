<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\Contact;
use App\Entity\PhoneNumber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListContactsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->executeStatement('DELETE FROM phone_number');
        $this->em->getConnection()->executeStatement('DELETE FROM contact');
    }

    public function testGetEmptyListReturns200(): void
    {
        $this->client->request('GET', '/api/contacts');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonStringEqualsJsonString(
            '[]',
            $this->client->getResponse()->getContent(),
        );
    }

    public function testGetReturnsContactsWithPhoneNumbers(): void
    {
        $contact = new Contact('John', 'Doe', '8.8.8.8', 'United States');
        $contact->addPhoneNumber(new PhoneNumber('+1234567890'));
        $this->em->persist($contact);
        $this->em->flush();

        $this->client->request('GET', '/api/contacts');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('John', $data[0]['firstName']);
        $this->assertSame('Doe', $data[0]['lastName']);
        $this->assertSame('United States', $data[0]['country']);
        $this->assertSame(['+1234567890'], $data[0]['phoneNumbers']);
    }

    public function testGetSortsByLastNameAsc(): void
    {
        $contactA = new Contact('Alice', 'Zimmerman', '1.1.1.1');
        $contactA->addPhoneNumber(new PhoneNumber('+111'));
        $contactB = new Contact('Bob', 'Adams', '2.2.2.2');
        $contactB->addPhoneNumber(new PhoneNumber('+222'));

        $this->em->persist($contactA);
        $this->em->persist($contactB);
        $this->em->flush();

        $this->client->request('GET', '/api/contacts?sort=lastName&order=asc');

        $data = json_decode(
            $this->client->getResponse()->getContent(),
            true,
        );
        $this->assertSame('Adams', $data[0]['lastName']);
        $this->assertSame('Zimmerman', $data[1]['lastName']);
    }

    public function testGetSortsByLastNameDesc(): void
    {
        $contactA = new Contact('Alice', 'Adams', '3.3.3.3');
        $contactA->addPhoneNumber(new PhoneNumber('+111'));
        $contactB = new Contact('Bob', 'Zimmerman', '4.4.4.4');
        $contactB->addPhoneNumber(new PhoneNumber('+222'));

        $this->em->persist($contactA);
        $this->em->persist($contactB);
        $this->em->flush();

        $this->client->request('GET', '/api/contacts?sort=lastName&order=desc');

        $data = json_decode(
            $this->client->getResponse()->getContent(),
            true,
        );
        $this->assertSame('Zimmerman', $data[0]['lastName']);
        $this->assertSame('Adams', $data[1]['lastName']);
    }
}
