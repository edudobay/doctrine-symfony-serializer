<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionClass;

class ReflectionClassMetadataFactory implements ClassMetadataFactoryInterface
{
    public function getClassMetadata(string $class): ClassMetadata
    {
        return $this->fromClass(new ReflectionClass($class));
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function fromClass(ReflectionClass $class): ClassMetadata
    {
        $properties = [];
        foreach ($class->getProperties() as $property) {
            $properties[$property->getName()] = $property;
        }

        $builder = new ClassMetadataBuilder();

        foreach ($properties as $domainPropertyName => $domainProperty) {
            foreach ($domainProperty->getAttributes(Attributes\Serializable::class) as $attribute) {
                /** @var Attributes\Serializable $serializable */
                $serializable = $attribute->newInstance();

                $backingPropertyName = $serializable->backingProperty ?? ('_' . $domainPropertyName);
                $backingProperty = $properties[$backingPropertyName] ?? throw new InvalidArgumentException("Non-existent property: $backingPropertyName");

                $domainProperty->setAccessible(true);
                $backingProperty->setAccessible(true);

                $builder->addProperty(
                    $domainProperty,
                    $serializable,
                    $backingProperty
                );

                break; // ignore duplicated attributes
            }
        }

        return $builder->build();
    }
}
