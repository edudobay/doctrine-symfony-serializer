<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

class ReflectionClassMetadataFactory implements ClassMetadataFactoryInterface
{
    public function __construct(private ?PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        $this->propertyTypeExtractor ??= new PhpDocExtractor();
    }

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

                $domainType = $domainProperty->getType();
                // Arrays: If item type was not explicitly given, try to extract it from docblock
                if ($domainType !== null && $domainType->getName() === 'array' && ! $serializable->arrayItemType) {
                    $serializable->arrayItemType = $this->getArrayItemType($class, $domainProperty);
                }

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

    private function getArrayItemType(ReflectionClass $class, ReflectionProperty $property): ?string
    {
        $types = $this->propertyTypeExtractor->getTypes($class->getName(), $property->getName());

        if ($types === null) {
            // Unable to determine type
            return null;
        }

        foreach ($types as $type) {
            $collectionValueTypes = $type->getCollectionValueTypes();
            foreach ($collectionValueTypes as $itemType) {
                if ($itemType->getBuiltinType() !== 'object') {
                    continue;
                }

                if ($itemType->isCollection()) {
                    continue; // Nested collection types are currently not supported
                }

                $itemClass = $itemType->getClassName();
                if (! $itemClass) {
                    continue;
                }

                return $itemClass;
            }
        }

        // Unable to determine type
        return null;
    }
}
