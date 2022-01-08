<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use PHPUnit\Framework\TestCase;

use function serialize;
use function unserialize;

class CacheTest extends TestCase
{
    public function test_class_metadata_should_be_serializable(): void
    {
        $factory = new ReflectionClassMetadataFactory();
        $metadata = $factory->getClassMetadata(Entities\EntityOne::class);

        $serialized = serialize($metadata);
        $reconstituted = unserialize($serialized);
        self::assertEquals($metadata, $reconstituted);
    }
}
