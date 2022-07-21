<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use Edudobay\DoctrineSerializable\Attributes\Serializable;

class EntityWithArrayProp
{
    public array $_ratings;

    /**
     * @param Rating[] $ratings
     */
    public function __construct(
        #[Serializable(arrayItemType: Rating::class)]
        public array $ratings,
    ) {
    }
}
