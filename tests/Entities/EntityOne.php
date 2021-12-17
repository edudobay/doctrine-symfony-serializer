<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

use DateTimeImmutable;
use Edudobay\DoctrineSerializable\Attributes\Serializable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

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
