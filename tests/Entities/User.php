<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

class User
{
    public function __construct(public string $username, public string $fullName)
    {
    }
}
