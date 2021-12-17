<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Edudobay\DoctrineSerializable\Attributes\Serializable;
use ReflectionProperty;

class FieldMapping
{
    public function __construct(
        /**
         * The property that is an actual part of your domain model — a domain object.
         */
        public ReflectionProperty $domainProperty,
        /**
         * The backing property that is used only for persistence.
         */
        public ReflectionProperty $backingProperty,
        public Serializable $serializable,
    ) {
    }
}
