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
        public ?string $dbProperty = null
    ) {
    }
}
