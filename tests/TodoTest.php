<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Attributes\Serializable;
use Edudobay\DoctrineSerializable\Examples\SerializerFactory;
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class TodoTest extends TestCase
{
    public function test_should_ignore_duplicate_attributes()
    {
        $this->markTestIncomplete('TODO');
    }

    public function test_context_annotation_is_used_in_normalization(): void
    {
        $e = new EntityOne(timestamp: new DateTimeImmutable('2021-09-29T05:44:31-07:00'));

        $this->handler()->serialize($e);

        self::assertEquals('31 44 08 29 09 2021', $e->_timestamp);
    }

    public function test_context_annotation_is_used_in_denormalization(): void
    {
        $e = $this->instantiate(EntityOne::class);
        $e->_timestamp = '31 44 08 29 09 2021';

        $this->handler()->deserialize($e);

        self::assertEquals(new DateTimeImmutable('2021-09-29T05:44:31-07:00'), $e->timestamp);
    }

    public function test_output_encoder_can_be_chosen(): void
    {
        $e = new EncodesToXmlString(user: new User('donald', 'Donald Duck'));

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
        $e = new EncodesToJsonString(user: new User('donald', 'Donald Duck'));

        $this->handler()->serialize($e);

        self::assertJsonStringEqualsJsonString(
            '{"username": "donald", "fullName": "Donald Duck"}',
            $e->_user
        );
    }

    public function test_nullable_attributes(): void
    {
        self::markTestIncomplete('TODO');
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

class EntityOne
{
    public function __construct(
        #[
            Serializable,
            Context([
                DateTimeNormalizer::FORMAT_KEY => 's i H d m Y',
                DateTimeNormalizer::TIMEZONE_KEY => '-04:00',
            ]),
        ]
        public DateTimeImmutable $timestamp
    ) {
    }

    public string $_timestamp;
}

class User
{
    public function __construct(public string $username, public string $fullName)
    {
    }
}

class EncodesToXmlString
{
    public function __construct(
        #[Serializable(format: 'xml', encodeToString: true)]
        public User $user,
    ) {
    }

    public string $_user;
}

class EncodesToJsonString
{
    public function __construct(
        #[Serializable(format: 'json', encodeToString: true)]
        public User $user,
    ) {
    }

    public string $_user;
}
