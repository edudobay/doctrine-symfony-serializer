<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Examples\SerializerFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
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
        $e = new Entities\EntityTwo(name: 'pencil');
        $this->handler()->serialize($e);
        self::assertSame('pencil', $e->_name);

        // 2. Set a null value
        $e->name = null;

        $this->handler()->serialize($e);

        self::assertNull($e->_name);
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
