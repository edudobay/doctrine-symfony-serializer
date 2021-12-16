<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    public static function serializer(): Serializer
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfo = new PropertyInfoExtractor(
            listExtractors: [$reflectionExtractor],
            typeExtractors: [$phpDocExtractor, $reflectionExtractor],
            descriptionExtractors: [$phpDocExtractor],
            accessExtractors: [$reflectionExtractor]
        );

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        return new Serializer(
            normalizers: [
                new DateTimeNormalizer(),
                new ObjectNormalizer(
                    classMetadataFactory: $classMetadataFactory,
                    nameConverter: $nameConverter,
                    propertyTypeExtractor: $propertyInfo,
                    classDiscriminatorResolver: new ClassDiscriminatorFromClassMetadata($classMetadataFactory),
                ),
                new ArrayDenormalizer(),
            ],
            encoders: [new JsonEncoder()],
        );
    }
}
