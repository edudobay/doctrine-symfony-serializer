<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;
use Edudobay\DoctrineSerializable\Attributes\Serializable;

#[ORM\Entity]
class User
{
    #[ORM\Column('address', type: 'json')]
    private array $_address = [];

    public function __construct(
        #[ORM\Id, ORM\Column]
        public string $id,
        #[ORM\Column]
        public string $name,
        // backingProperty: '_address' is the default (inferred from the property name), but it is
        // kept here to illustrate.
        #[Serializable(backingProperty: '_address')]
        public Address $address,
    ) {
    }
}
