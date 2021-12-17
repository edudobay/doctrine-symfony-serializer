<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

class ClassMetadata
{
    public function __construct(
        /** @var FieldMapping[] */
        private array $fields,
    ) {
    }

    /**
     * @return FieldMapping[]
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
