<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Edudobay\DoctrineSerializable\Attributes\Serializable;
use ReflectionProperty;

class ClassMetadataBuilder
{
    /** @var FieldMapping[] */
    private array $fields = [];

    public function addProperty(
        ReflectionProperty $property,
        Serializable $serializable,
        ReflectionProperty $backingProperty,
    ): self {
        $this->fields[] = new FieldMapping(
            domainProperty: $property,
            dbProperty: $backingProperty,
            serializable: $serializable
        );

        return $this;
    }

    public function build(): ClassMetadata
    {
        return new ClassMetadata($this->fields);
    }
}
