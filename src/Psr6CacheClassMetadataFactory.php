<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Psr\Cache\CacheItemPoolInterface;

use function urlencode;

class Psr6CacheClassMetadataFactory implements ClassMetadataFactoryInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private ClassMetadataFactoryInterface $factory
    ) {
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        // Remember that characters \ and @ (present in class names through namespace separators and anonymous class markers)
        // are reserved by PSR-6.
        $key = urlencode($class);

        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            /** @var ClassMetadata */
            return $item->get();
        }

        $metadata = $this->factory->getClassMetadata($class);
        $item->set($metadata);
        $this->cache->save($item);

        return $metadata;
    }
}
