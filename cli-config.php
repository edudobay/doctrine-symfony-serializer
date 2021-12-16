<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Edudobay\DoctrineSerializable\Examples\Application;
use Edudobay\DoctrineSerializable\Examples\DoctrineFactory;

require_once __DIR__ . '/vendor/autoload.php';

return ConsoleRunner::createHelperSet(DoctrineFactory::entityManager(Application::create()));
