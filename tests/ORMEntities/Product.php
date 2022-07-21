<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\ORMEntities;

use Doctrine\ORM\Mapping as ORM;
use Edudobay\DoctrineSerializable\Attributes\Serializable;

#[ORM\Entity, ORM\Table]
class Product
{
    #[ORM\Column('categories', type: 'json')]
    public array $_categories;

    public function __construct(
        #[ORM\Id, ORM\Column]
        public string $id,

        #[ORM\Column]
        public string $name,

        #[Serializable]
        /** @var Category[] */
        public array $categories,
    ) {
    }
}
