<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use Edudobay\DoctrineSerializable\Attributes\Serializable;
use Edudobay\DoctrineSerializable\Tests\Entities;

class EncodesToJsonString
{
    public function __construct(
        #[Serializable(format: 'json', encodeToString: true)]
        public Entities\User $user,
    ) {
    }

    public string $_user;
}
