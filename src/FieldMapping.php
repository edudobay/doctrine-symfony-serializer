<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Edudobay\DoctrineSerializable\Attributes\Serializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * @psalm-type MethodArray = array{0: class-string, 1: string}
 */
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

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        return [
            'domainProperty' => [
                $this->domainProperty->getDeclaringClass()->getName(),
                $this->domainProperty->getName()
            ],
            'backingProperty' => [
                $this->backingProperty->getDeclaringClass()->getName(),
                $this->backingProperty->getName()
            ],
            'serializable' => $this->serializable,
        ];
    }

    /** @param array{domainProperty: MethodArray, backingProperty: MethodArray, serializable: Serializable} $data */
    public function __unserialize(array $data): void
    {
        [$class, $property] = $data['domainProperty'];
        $this->domainProperty = (new ReflectionClass($class))->getProperty($property);
        $this->domainProperty->setAccessible(true);

        [$class, $property] = $data['backingProperty'];
        $this->backingProperty = (new ReflectionClass($class))->getProperty($property);
        $this->backingProperty->setAccessible(true);

        $this->serializable = $data['serializable'];
    }
}
