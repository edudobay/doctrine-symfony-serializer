<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\Setup;

class DoctrineFactory
{
    public static function entityManager(Application $app): EntityManagerInterface
    {
        $rootPath = $app->rootPath;

        $cache = DoctrineProvider::wrap($app->cache);

        $config = Setup::createConfiguration(
            isDevMode: true,
            proxyDir: "$rootPath/var/proxies",
            cache: $cache,
        );

        $config->setMetadataDriverImpl(new AttributeDriver(["$rootPath/src/Examples/Entity"]));

        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'path' => "$rootPath/var/db.sqlite",
        ];

        return EntityManager::create($connectionParams, $config);
    }
}
