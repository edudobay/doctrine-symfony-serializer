<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

use function get_debug_type;
use function in_array;

class ReflectionClassMetadataFactory implements ClassMetadataFactoryInterface
{
    private PropertyTypeExtractorInterface $propertyTypeExtractor;

    public function __construct(?PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        $this->propertyTypeExtractor = $propertyTypeExtractor ?? new PhpDocExtractor();
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

                if ($domainType !== null && ! $domainType instanceof ReflectionNamedType) {
                    throw new InvalidArgumentException(sprintf(
                        "Type '%s' is not supported",
                        get_debug_type($domainType)
                    ));
                }

                // We won't try to deserialize builtin types, only objects
                $allowedBuiltInTypes = ['array'];
                if ($domainType?->isBuiltin() && ! in_array($domainType->getName(), $allowedBuiltInTypes, true)) {
                    throw new InvalidArgumentException("Type is builtin: {$domainType->getName()}, ");
                }

                // Arrays: If item type was not explicitly given, try to extract it from docblock
                if ($domainType?->getName() === 'array' && ! $serializable->arrayItemType) {
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

    /**
     * @param ReflectionClass<object> $class
     * @return null|class-string
     */
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
                if ($itemType->isCollection()) {
                    continue; // Nested collection types are currently not supported
                }

                if ($itemType->getBuiltinType() !== 'object') {
                    continue;
                }

                $itemClass = $itemType->getClassName();
                if ($itemClass === null || $itemClass === '') {
                    // Should not happen if getBuiltinType() === 'object'
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }

                /** @var class-string $itemClass */
                return $itemClass;
            }
        }

        // Unable to determine type
        return null;
    }
}
