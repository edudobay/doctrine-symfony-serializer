<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Edudobay\DoctrineSerializable\Examples\SerializerFactory;
use Edudobay\DoctrineSerializable\PersistenceEventSubscriber;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use Edudobay\DoctrineSerializable\Tests\ORMEntities\Category;
use Edudobay\DoctrineSerializable\Tests\ORMEntities\Product;
use PHPUnit\Framework\TestCase;

use function dirname;

class DoctrineEventsTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [dirname(__DIR__, 2) . '/tests/ORMEntities'],
            isDevMode: true,
        );

        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'path' => ":memory:",
        ];

        $connection = DriverManager::getConnection($connectionParams);

        $this->entityManager = new EntityManager($connection, $config);
        // Create tables in the test database
        (new SchemaTool($this->entityManager))->createSchema([
            $this->entityManager->getClassMetadata(Product::class),
        ]);

        $serializer = SerializerFactory::serializer();
        $handler = new SerializationHandler($serializer, new ReflectionClassMetadataFactory());

        $this->entityManager->getEventManager()->addEventSubscriber(
            new PersistenceEventSubscriber($handler)
        );
    }

    public function test_persist_entity()
    {
        $this->entityManager->wrapInTransaction(function () {
            $this->entityManager->persist(new Product(
                id: 'pr-1479',
                name: 'Water Bottle 500 ml',
                categories: [new Category('cat-012', 'Drinks')],
            ));
        });

        $db = $this->entityManager->getConnection();
        $data = $db->fetchAllAssociative('SELECT * FROM Product');

        self::assertEquals(
            [
                [
                    'id' => 'pr-1479',
                    'name' => 'Water Bottle 500 ml',
                    'categories' => '[{"id":"cat-012","name":"Drinks"}]',
                ],
            ],
            $data
        );
    }

    public function test_retrieve_entity()
    {
        $db = $this->entityManager->getConnection();
        $qb = $db->createQueryBuilder();
        $qb
            ->insert('Product')
            ->values([
                'id' => $qb->createPositionalParameter('pr-19945'),
                'name' => $qb->createPositionalParameter('Watermelon'),
                'categories' => $qb->createPositionalParameter('[{"id":"cat-008","name":"Fruits & Vegetables"}]'),
            ])
            ->executeStatement();

        $expectedProduct = new Product(
            id: 'pr-19945',
            name: 'Watermelon',
            categories: [new Category('cat-008', 'Fruits & Vegetables')],
        );

        /** @var Product $product */
        $product = $this->entityManager->find(Product::class, 'pr-19945');
        unset($product->_categories); // Attribute used only for backing storage

        self::assertEquals($expectedProduct, $product);

        // Now modify the Product and verify that it was correctly updated in the database.

        $this->entityManager->wrapInTransaction(function () use ($product) {
            $product->name = 'Melon';

            $this->entityManager->flush();
        });

        $result = $db->fetchAssociative('SELECT name FROM Product WHERE id = ?', ['pr-19945']);
        self::assertEquals(['name' => 'Melon'], $result);
    }
}
