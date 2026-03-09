<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ContactResponse;
use App\DTO\CreateContactRequestDTO;
use App\Message\CreateContactMessage;
use App\Repository\ContactRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ContactService
{
    // TTL 10с — компроміс між навантаженням на БД та актуальністю даних.
    // Без ручної інвалідації, бо при високому write-навантаженні кеш втрачає сенс.
    private const int CACHE_TTL = 10;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ContactRepository $contactRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function createContact(CreateContactRequestDTO $dto, string $ip): void
    {
        $this->messageBus->dispatch(new CreateContactMessage(
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            phoneNumbers: $dto->phoneNumbers,
            ip: $ip,
        ));
    }

    /**
     * @return ContactResponse[]
     */
    public function getListOfContacts(string $sort = 'lastName', string $order = 'asc'): array
    {
        $cacheKey = sprintf('contacts_list_%s_%s', $sort, $order);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($sort, $order): array {
            $item->expiresAfter(self::CACHE_TTL);

            $contacts = $this->contactRepository->findAllSorted($sort, $order);

            return array_map(
                static fn ($contact) => ContactResponse::fromEntity($contact),
                $contacts,
            );
        });
    }
}
