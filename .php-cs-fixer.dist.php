<?php

$includeDirs = [
    __DIR__ . '/src',
];

$finder = PhpCsFixer\Finder::create()
    ->in($includeDirs);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
