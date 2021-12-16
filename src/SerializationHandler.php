<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionNamedType;
use Symfony\Component\Serializer\Serializer;

class SerializationHandler
{
    public function __construct(
        private Serializer $serializer,
        private ClassMetadataFactoryInterface $metadataFactory
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function serialize(object $entity, string $format = null, array $context = []): void
    {
        $metadata = $this->metadataFactory->getClassMetadata($entity::class);

        foreach ($metadata->fields() as $mapping) {
            /** @var mixed $domainValue */
            $domainValue = $mapping->domainProperty->getValue($entity);
            $dbValue = $this->serializer->normalize($domainValue, $format, $context);
            $mapping->dbProperty->setValue($entity, $dbValue);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function deserialize(object $entity, string $format = null, array $context = []): void
    {
        $className = $entity::class;
        $metadata = $this->metadataFactory->getClassMetadata($className);

        foreach ($metadata->fields() as $mapping) {
            /** @var mixed $dbValue */
            $dbValue = $mapping->dbProperty->getValue($entity);

            $type = $mapping->domainProperty->getType();
            if ($type instanceof ReflectionNamedType) {
                $propertyType = $type->getName();
                if ($type->isBuiltin()) {
                    throw new InvalidArgumentException("Type is builtin: $propertyType");
                }
            } else {
                throw new InvalidArgumentException("Not implemented: how to convert $type");
            }

            /** @var mixed $domainValue */
            $domainValue = $this->serializer->denormalize(
                $dbValue,
                $propertyType,
                $format,
                $context
            );
            $mapping->domainProperty->setValue($entity, $domainValue);
        }
    }
}
