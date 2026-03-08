<?php

declare(strict_types=1);

namespace App\Message;

final class CreateContactMessage
{
    /**
     * @param string[] $phoneNumbers
     */
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly array $phoneNumbers,
        public readonly string $ip,
    ) {
    }
}
