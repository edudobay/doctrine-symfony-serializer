<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use ReflectionProperty;

class FieldMapping
{
    public function __construct(
        public ReflectionProperty $domainProperty,
        public ReflectionProperty $dbProperty,
    ) {
    }
}
