<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use ReflectionProperty;

use function array_map;

class ClassMetadata
{
    public function __construct(
        /** @var FieldMapping[] */
        private array $fields,
    ) {
    }

    /**
     * @param array<array-key, array{0: ReflectionProperty, 1: ReflectionProperty}> $pairs
     * @see ReflectionProperty
     */
    public static function fromPairs(array $pairs): self
    {
        return new self(array_map(
            fn (array $pair) => new FieldMapping($pair[0], $pair[1]),
            $pairs
        ));
    }

    /**
     * @return FieldMapping[]
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
