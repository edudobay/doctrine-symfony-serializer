<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

interface ClassMetadataFactoryInterface
{
    /**
     * @param class-string $class
     */
    public function getClassMetadata(string $class): ClassMetadata;
}
