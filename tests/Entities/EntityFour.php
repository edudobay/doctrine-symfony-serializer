<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Attributes\Serializable;

class EntityFour
{
    public function __construct(
        #[Serializable]
        public User $user
    ) {
    }

    private array $_user;
}
