<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Examples\SerializerFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use Edudobay\DoctrineSerializable\Tests\Entities\EntityWithArrayHintedProp;
use Edudobay\DoctrineSerializable\Tests\Entities\EntityWithArrayProp;
use Edudobay\DoctrineSerializable\Tests\Entities\EntityWithUntypedArrayProp;
use Edudobay\DoctrineSerializable\Tests\Entities\Rating;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SerializationHandlerTest extends TestCase
{
    public function test_context_annotation_is_used_in_normalization(): void
    {
        $e = new Entities\EntityOne(timestamp: new DateTimeImmutable('2021-09-29T05:44:31-07:00'));

        $this->handler()->serialize($e);

        self::assertSame('31 44 08 29 09 2021', $e->_timestamp);
    }

    public function test_context_annotation_is_used_in_denormalization(): void
    {
        $e = $this->instantiate(Entities\EntityOne::class);
        $e->_timestamp = '31 44 08 29 09 2021';

        $this->handler()->deserialize($e);

        self::assertEquals(new DateTimeImmutable('2021-09-29T05:44:31-07:00'), $e->timestamp);
    }

    public function test_output_encoder_can_be_chosen(): void
    {
        $e = new Entities\EncodesToXmlString(user: new Entities\User('donald', 'Donald Duck'));

        $this->handler()->serialize($e);

        /** <response> is the default root node name in {@link XmlEncoder} */
        self::assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
            <response>
                <username>donald</username>
                <fullName>Donald Duck</fullName>
            </response>',
            $e->_user
        );
    }

    public function test_can_output_JSON_string_instead_of_array(): void
    {
        $e = new Entities\EncodesToJsonString(user: new Entities\User('donald', 'Donald Duck'));

        $this->handler()->serialize($e);

        self::assertJsonStringEqualsJsonString(
            '{"username": "donald", "fullName": "Donald Duck"}',
            $e->_user
        );
    }

    public function test_can_deserialize_JSON_string_instead_of_array(): void
    {
        $e = $this->instantiate(Entities\EncodesToJsonString::class);
        $e->_user = '{"username": "mickey42", "fullName": "Mickey Mouse"}';

        $this->handler()->deserialize($e);

        self::assertEquals(new Entities\User('mickey42', 'Mickey Mouse'), $e->user);
    }

    // TODO: What else can we verify about nullable properties?
    public function test_null_property_is_serialized_to_PHP_null(): void
    {
        // 1. Set a non-null value
        $e = new Entities\EntityTwo(element: new Entities\Element('pencil'));
        $this->handler()->serialize($e);
        self::assertSame(['name' => 'pencil'], $e->_element);

        // 2. Set a null value
        $e->element = null;

        $this->handler()->serialize($e);

        self::assertNull($e->_element);
    }

    public function test_can_serialize_to_private_backing_property(): void
    {
        $user = new Entities\User('ruth@example.com', 'Ruth Davis');
        $e = new Entities\EntityFour(user: $user);
        $this->handler()->serialize($e);

        $e->user = new Entities\User('dummy@user.com', 'Should Be Overwritten');
        $this->handler()->deserialize($e);

        self::assertSame('Ruth Davis', $e->user->fullName);
    }

    public function test_can_serialize_array_of_objects_with_arrayItemType_attribute(): void
    {
        $entity = new EntityWithArrayProp([
            new Rating(5, 'Speed'),
            new Rating(3, 'Design'),
            new Rating(4, 'Cost'),
        ]);

        $this->handler()->serialize($entity);

        self::assertEquals(
            [
                ['score' => 5, 'category' => 'Speed'],
                ['score' => 3, 'category' => 'Design'],
                ['score' => 4, 'category' => 'Cost']
            ],
            $entity->_ratings
        );
    }

    public function test_can_deserialize_to_array_of_objects_with_arrayItemType_attribute(): void
    {
        $entity = new EntityWithArrayProp(ratings: []);

        $entity->_ratings = [
            ['score' => 2, 'category' => 'Simplicity'],
        ];

        $this->handler()->deserialize($entity);

        self::assertEquals(
            [new Rating(2, 'Simplicity')],
            $entity->ratings
        );
    }

    public function test_can_deserialize_array_of_objects_with_docblock_type(): void
    {
        $entity = new EntityWithArrayHintedProp([
            new Rating(5, 'Speed'),
            new Rating(3, 'Design'),
            new Rating(4, 'Cost'),
        ]);

        $this->handler()->serialize($entity);

        $this->handler()->deserialize($entity);

        self::assertContainsOnlyInstancesOf(Rating::class, $entity->ratings);
    }

    public function test_cannot_deserialize_array_of_objects_without_any_type_hint(): void
    {
        $entity = new EntityWithUntypedArrayProp([
            new Rating(5, 'Speed'),
            new Rating(3, 'Design'),
            new Rating(4, 'Cost'),
        ]);

        $this->handler()->serialize($entity);

        $this->expectException(InvalidArgumentException::class);
        $this->handler()->deserialize($entity);
    }

    private function handler(): SerializationHandler
    {
        return new SerializationHandler(
            SerializerFactory::serializer(),
            new ReflectionClassMetadataFactory()
        );
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    private function instantiate(string $class)
    {
        $reflectionClass = new ReflectionClass($class);
        return $reflectionClass->newInstanceWithoutConstructor();
    }
}
