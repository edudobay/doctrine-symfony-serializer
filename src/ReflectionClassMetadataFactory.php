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

        $map = [];
        foreach ($properties as $domainModelPropertyName => $domainModelProperty) {
            foreach ($domainModelProperty->getAttributes(Attributes\Serializable::class) as $attribute) {
                /** @var Attributes\Serializable $field */
                $field = $attribute->newInstance();

                $dbMappedPropertyName = $field->dbProperty ?? ('_' . $domainModelPropertyName);
                $dbMappedProperty = $properties[$dbMappedPropertyName] ?? throw new InvalidArgumentException("Non-existent property: $dbMappedPropertyName");

                $domainModelProperty->setAccessible(true);
                $dbMappedProperty->setAccessible(true);

                $map[] = [$domainModelProperty, $dbMappedProperty];

                break; // ignore duplicated attributes
            }
        }

        return ClassMetadata::fromPairs($map);
    }
}
