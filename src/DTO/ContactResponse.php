<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contact;

readonly class ContactResponse
{
    /**
     * @param string[] $phoneNumbers
     */
    public function __construct(
        public ?int $id,
        public string $firstName,
        public string $lastName,
        public string $ip,
        public ?string $country,
        public string $createdAt,
        public array $phoneNumbers,
    ) {
    }

    public static function fromEntity(Contact $contact): self
    {
        $phoneNumbers = [];
        foreach ($contact->getPhoneNumbers() as $phone) {
            $phoneNumbers[] = $phone->getNumber();
        }

        return new self(
            id: $contact->getId(),
            firstName: $contact->getFirstName(),
            lastName: $contact->getLastName(),
            ip: $contact->getIp(),
            country: $contact->getCountry(),
            createdAt: $contact->getCreatedAt()->format(\DateTimeInterface::ATOM),
            phoneNumbers: $phoneNumbers,
        );
    }
}
