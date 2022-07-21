<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable\Tests\Entities;

class Rating
{
    public function __construct(public int $score, public string $category)
    {
    }
}
