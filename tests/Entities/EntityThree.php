<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Attributes\Serializable;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\Tests\Psr6CacheClassMetadataFactoryTest;

/**
 * This entity should never be loaded directly from the {@link ReflectionClassMetadataFactory}.
 *
 * @see Psr6CacheClassMetadataFactoryTest::test_get_serialized_metadata()
 */
class EntityThree
{
    public function __construct(
        #[Serializable]
        public User $user
    ) {
    }

    private array $_user;
}
