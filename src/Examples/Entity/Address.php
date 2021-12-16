<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples\Entity;

class Address
{
    public function __construct(public string $city, public string $state)
    {
    }
}
