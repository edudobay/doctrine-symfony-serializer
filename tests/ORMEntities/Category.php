<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\ORMEntities;

class Category
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
