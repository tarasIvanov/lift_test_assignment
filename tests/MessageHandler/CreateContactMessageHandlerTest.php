<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Contact;
use App\Message\CreateContactMessage;
use App\MessageHandler\CreateContactMessageHandler;
use App\Service\IpGeolocationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateContactMessageHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private IpGeolocationServiceInterface&MockObject $geolocationService;
    private CreateContactMessageHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->geolocationService = $this->createMock(
            IpGeolocationServiceInterface::class,
        );

        $this->handler = new CreateContactMessageHandler(
            $this->entityManager,
            $this->geolocationService,
        );
    }

    public function testHandleCreatesContactWithCountry(): void
    {
        $this->geolocationService
            ->expects($this->once())
            ->method('getCountry')
            ->with('8.8.8.8')
            ->willReturn('United States');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Contact $contact): bool {
                return $contact->getFirstName() === 'John'
                    && $contact->getLastName() === 'Doe'
                    && $contact->getCountry() === 'United States'
                    && $contact->getPhoneNumbers()->count() === 2;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $message = new CreateContactMessage(
            firstName: 'John',
            lastName: 'Doe',
            phoneNumbers: ['+1234567890', '+0987654321'],
            ip: '8.8.8.8',
        );

        ($this->handler)($message);
    }

    public function testHandleCreatesContactWithNullCountryOnLookupFailure(): void
    {
        $this->geolocationService
            ->expects($this->once())
            ->method('getCountry')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Contact $contact): bool {
                return $contact->getCountry() === null;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $message = new CreateContactMessage(
            firstName: 'Jane',
            lastName: 'Doe',
            phoneNumbers: ['+1111111111'],
            ip: '0.0.0.0',
        );

        ($this->handler)($message);
    }

    public function testHandlePersistsPhoneNumbers(): void
    {
        $this->geolocationService
            ->method('getCountry')
            ->willReturn('Germany');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Contact $contact): bool {
                $numbers = [];
                foreach ($contact->getPhoneNumbers() as $phone) {
                    $numbers[] = $phone->getNumber();
                }

                return $numbers === ['+111', '+222', '+333'];
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $message = new CreateContactMessage(
            firstName: 'Max',
            lastName: 'Mustermann',
            phoneNumbers: ['+111', '+222', '+333'],
            ip: '1.2.3.4',
        );

        ($this->handler)($message);
    }
}
