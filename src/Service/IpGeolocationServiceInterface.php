<?php

declare(strict_types=1);

namespace App\Service;

interface IpGeolocationServiceInterface
{
    public function getCountry(string $ip): ?string;
}
