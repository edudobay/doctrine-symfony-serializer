<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Examples;

use Doctrine\ORM\EntityManagerInterface;
use Edudobay\DoctrineSerializable\Examples\Entity\Address;
use Edudobay\DoctrineSerializable\Examples\Entity\User;
use Edudobay\DoctrineSerializable\Psr6CacheClassMetadataFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use Edudobay\DoctrineSerializable\PersistenceEventSubscriber;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

class Example
{
    /**
     * @psalm-suppress ForbiddenCode - this is just example code
     */
    public static function main(): void
    {
        $app = Application::create();
        $em = DoctrineFactory::entityManager($app);

        $serializer = SerializerFactory::serializer();

        $handler = new SerializationHandler(
            $serializer,
            new Psr6CacheClassMetadataFactory($app->cache, new ReflectionClassMetadataFactory())
        );

        $subscriber = new PersistenceEventSubscriber($handler);

        $em->getEventManager()->addEventSubscriber($subscriber);

        $em->wrapInTransaction(function (EntityManagerInterface $em) {
            $em->persist(new User('1', 'Eduardo', new Address('São Paulo', 'SP')));
            $em->persist(new User('2', 'Luciana', new Address('Macaé', 'RJ')));
        });

        $em->clear();
        $r = $em->getRepository(User::class);
        var_dump($r->findAll());
    }
}

Example::main();
