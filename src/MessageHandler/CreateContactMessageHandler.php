<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contact;
use App\Entity\PhoneNumber;
use App\Message\CreateContactMessage;
use App\Service\IpGeolocationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateContactMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IpGeolocationServiceInterface $geolocationService,
    ) {
    }

    public function __invoke(CreateContactMessage $message): void
    {
        $country = $this->geolocationService->getCountry($message->ip);

        $contact = new Contact(
            $message->firstName,
            $message->lastName,
            $country,
        );

        foreach ($message->phoneNumbers as $number) {
            $contact->addPhoneNumber(new PhoneNumber($number));
        }

        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }
}
