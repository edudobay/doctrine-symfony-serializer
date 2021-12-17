<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Edudobay\DoctrineSerializable\Attributes\Serializable;
use ReflectionProperty;

class FieldMapping
{
    public function __construct(
        public ReflectionProperty $domainProperty,
        public ReflectionProperty $dbProperty,
        public Serializable $serializable,
    ) {
    }
}
