<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionNamedType;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Serializer;

use function array_merge;
use function get_debug_type;

class SerializationHandler
{
    public function __construct(
        private Serializer $serializer,
        private ClassMetadataFactoryInterface $metadataFactory
    ) {
    }

    public function serialize(object $entity): void
    {
        $metadata = $this->metadataFactory->getClassMetadata($entity::class);

        foreach ($metadata->fields() as $mapping) {
            /** @var mixed $domainValue */
            $domainValue = $mapping->domainProperty->getValue($entity);

            $context = [];
            foreach ($mapping->domainProperty->getAttributes(Context::class) as $item) {
                /** @var Context $attr */
                $attr = $item->newInstance();
                $context = array_merge($context, $attr->getContext(), $attr->getNormalizationContext());
            }

            $format = $mapping->serializable->format;

            $dbValue = $mapping->serializable->encodeToString ?
                $this->serializer->serialize($domainValue, $format, $context) :
                $this->serializer->normalize($domainValue, $format, $context);

            $mapping->backingProperty->setValue($entity, $dbValue);
        }
    }

    public function deserialize(object $entity): void
    {
        $className = $entity::class;
        $metadata = $this->metadataFactory->getClassMetadata($className);

        foreach ($metadata->fields() as $mapping) {
            /** @var mixed $dbValue */
            $dbValue = $mapping->backingProperty->getValue($entity);

            $propertyType = $this->getPropertyType($mapping);

            $domainPropertyType = $mapping->domainProperty->getType();
            if ($domainPropertyType === null) {
                // This should not be reached in normal conditions - this condition is checked in the ReflectionClassMetadataFactory.
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException(sprintf(
                    "Not implemented: property '%s' has no type",
                    $mapping->domainProperty->name,
                ));
                // @codeCoverageIgnoreEnd
            }

            $context = [];
            foreach ($mapping->domainProperty->getAttributes(Context::class) as $item) {
                /** @var Context $attr */
                $attr = $item->newInstance();
                $context = array_merge($context, $attr->getContext(), $attr->getDenormalizationContext());
            }


            if ($domainPropertyType->allowsNull() && $dbValue === null) {
                $domainValue = null;
            } else {
                $format = $mapping->serializable->format;

                /** @var mixed $domainValue */
                $domainValue = $mapping->serializable->encodeToString ?
                    $this->serializer->deserialize($dbValue, $propertyType, $format, $context) :
                    $this->serializer->denormalize($dbValue, $propertyType, $format, $context);
            }

            $mapping->domainProperty->setValue($entity, $domainValue);
        }
    }

    private function getPropertyType(FieldMapping $mapping): string
    {
        $type = $mapping->domainProperty->getType();
        if (! $type instanceof ReflectionNamedType) {
            // This should not be reached in normal conditions - this condition is checked in the ReflectionClassMetadataFactory.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Not implemented: how to convert " . get_debug_type($type));
            // @codeCoverageIgnoreEnd
        }

        $propertyType = $type->getName();

        // Handle array types
        if ($type->getName() === 'array') {
            $itemType = $mapping->serializable->arrayItemType;
            if (! $itemType) {
                throw new InvalidArgumentException("For array types, 'arrayItemType' is required");
            }

            return $itemType . '[]';
        }

        return $propertyType;
    }
}
