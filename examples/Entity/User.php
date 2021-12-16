<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;
use Edudobay\DoctrineSerializable\Attributes\Serializable;

#[ORM\Entity]
class User
{
    #[ORM\Column('address', type: 'json')]
    private array|\ArrayObject $_object = [];

    public function __construct(
        #[ORM\Id, ORM\Column]
        public string $id,
        #[ORM\Column]
        public string $name,
        #[Serializable(dbProperty: '_object')]
        public Address $address,
    ) {
    }
}
