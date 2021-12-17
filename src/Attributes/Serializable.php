<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Serializable
{
    public function __construct(
        /**
         * If null, assume '_' + current property name
         */
        public ?string $dbProperty = null,

        /**
         * @see \Symfony\Component\Serializer\Serializer::serialize() for the meaning of $format
         */
        public string $format = 'json',

        /**
         * If true, encode to a string in the target format (e.g. JSON). If false, just normalize to a primitive type
         * (usually an array, but not always).
         *
         * TODO: Explain this difference, especially the raw string vs. JSON string part.
         */
        public bool $encodeToString = false,
    ) {
    }
}
