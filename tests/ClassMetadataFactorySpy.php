<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use Edudobay\DoctrineSerializable\ClassMetadata;
use Edudobay\DoctrineSerializable\ClassMetadataFactoryInterface;

class ClassMetadataFactorySpy implements ClassMetadataFactoryInterface
{
    public int $timesCalled = 0;

    public function __construct(private ClassMetadataFactoryInterface $spied)
    {
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        $this->timesCalled++;
        return $this->spied->getClassMetadata($class);
    }
}
