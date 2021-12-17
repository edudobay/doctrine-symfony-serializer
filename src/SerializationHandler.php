<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use InvalidArgumentException;
use ReflectionNamedType;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Serializer;

use function array_merge;

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

    /**
     * @param array<string, mixed> $context
     */
    public function deserialize(object $entity, string $format = null, array $context = []): void
    {
        $className = $entity::class;
        $metadata = $this->metadataFactory->getClassMetadata($className);

        foreach ($metadata->fields() as $mapping) {
            /** @var mixed $dbValue */
            $dbValue = $mapping->backingProperty->getValue($entity);

            $type = $mapping->domainProperty->getType();
            if ($type instanceof ReflectionNamedType) {
                $propertyType = $type->getName();
                if ($type->isBuiltin()) {
                    throw new InvalidArgumentException("Type is builtin: $propertyType");
                }
            } else {
                throw new InvalidArgumentException("Not implemented: how to convert $type");
            }

            foreach ($mapping->domainProperty->getAttributes(Context::class) as $item) {
                /** @var Context $attr */
                $attr = $item->newInstance();
                $context = array_merge($context, $attr->getContext(), $attr->getDenormalizationContext());
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
