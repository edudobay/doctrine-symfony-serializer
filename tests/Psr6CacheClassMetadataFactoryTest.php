<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use Edudobay\DoctrineSerializable\Examples\SerializerFactory;
use Edudobay\DoctrineSerializable\Psr6CacheClassMetadataFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;

class Psr6CacheClassMetadataFactoryTest extends TestCase
{
    private string $cacheDir;
    private Filesystem $fs;

    public function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/phpunit-doctrine-serializable';
        $this->fs = new Filesystem();
        $this->fs->remove($this->cacheDir);
        $this->fs->mkdir($this->cacheDir);
    }

    public function tearDown(): void
    {
        $this->fs->remove($this->cacheDir);
    }

    public function test_cache_passes_through()
    {
        $factorySpy = new ClassMetadataFactorySpy(new ReflectionClassMetadataFactory());
        $factory = new Psr6CacheClassMetadataFactory(new ArrayAdapter(storeSerialized: false), $factorySpy);

        $metadata = $factory->getClassMetadata(Entities\EntityOne::class);
        self::assertSame(
            Entities\EntityOne::class,
            $metadata->fields()[0]->backingProperty->getDeclaringClass()->getName()
        );

        self::assertSame(1, $factorySpy->timesCalled);

        $factory->getClassMetadata(Entities\EntityOne::class);
        self::assertSame(1, $factorySpy->timesCalled);

        $factory->getClassMetadata(Entities\User::class);
        self::assertSame(2, $factorySpy->timesCalled);
    }

    public function test_get_serialized_metadata()
    {
        $cache = new FilesystemAdapter(directory: $this->cacheDir);
        $factorySpy = new ClassMetadataFactorySpy(new ReflectionClassMetadataFactory());
        $factory = new Psr6CacheClassMetadataFactory($cache, $factorySpy);

        $factory->getClassMetadata(Entities\EntityThree::class);
        $factorySpy->reset();

        $metadata = $factory->getClassMetadata(Entities\EntityThree::class);

        self::assertSame(0, $factorySpy->timesCalled);
    }

    public function test_can_serialize_to_private_backing_property(): void
    {
        $cache = new FilesystemAdapter(directory: $this->cacheDir);
        $factorySpy = new ClassMetadataFactorySpy(new ReflectionClassMetadataFactory());
        $factory = new Psr6CacheClassMetadataFactory($cache, $factorySpy);

        $factory->getClassMetadata(Entities\EntityThree::class);
        $factory->getClassMetadata(Entities\User::class);
        $factorySpy->reset();

        $handler = new SerializationHandler(SerializerFactory::serializer(), $factory);

        $user = new Entities\User('ruth@example.com', 'Ruth Davis');
        $e = new Entities\EntityThree(user: $user);
        $handler->serialize($e);

        $e->user = new Entities\User('dummy@user.com', 'Should Be Overwritten');
        $handler->deserialize($e);

        self::assertSame('Ruth Davis', $e->user->fullName);

        self::assertSame(0, $factorySpy->timesCalled);
    }

}
