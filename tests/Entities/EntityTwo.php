<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use Edudobay\DoctrineSerializable\Attributes\Serializable;

class EntityTwo
{
    public function __construct(
        #[Serializable]
        public ?Element $element
    ) {
    }

    public ?array $_element;
}

class Element
{
    public function __construct(public ?string $name)
    {
    }
}
