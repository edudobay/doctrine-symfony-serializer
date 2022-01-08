<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use Edudobay\DoctrineSerializable\Psr6CacheClassMetadataFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class Psr6CacheClassMetadataFactoryTest extends TestCase
{

    public function test_cache_passes_through()
    {
        $factorySpy = new ClassMetadataFactorySpy(new ReflectionClassMetadataFactory());
        // TODO: Reflection objects cannot be cached. Can we cache anything useful?
        $factory = new Psr6CacheClassMetadataFactory(new ArrayAdapter(storeSerialized: false), $factorySpy);

        $metadata = $factory->getClassMetadata(Entities\EntityOne::class);
        self::assertSame(
            Entities\EntityOne::class,
            $metadata->fields()[0]->backingProperty->getDeclaringClass()->getName()
        );

        self::assertSame(1, $factorySpy->timesCalled);

        $factory->getClassMetadata(Entities\EntityOne::class);
        self::assertSame(1, $factorySpy->timesCalled);
    }
}
