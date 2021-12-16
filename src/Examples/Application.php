<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples;

use Composer\InstalledVersions;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function realpath;

class Application
{
    public function __construct(
        public string $rootPath,
        public string $cachePath,
        public CacheItemPoolInterface $cache,
    ) {
    }

    public static function create(): self
    {
        return new self(
            rootPath: ($rootPath = realpath(InstalledVersions::getRootPackage()['install_path']))
                ?: throw new InvalidArgumentException("Root path does not exist: $rootPath"),
            cachePath: $cachePath = "$rootPath/var/cache",
            cache: new ArrayAdapter(),
        );
    }
}
