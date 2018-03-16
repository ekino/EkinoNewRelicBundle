<?php

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/')
            ->exclude('vendor')
            ->name('*.php')
    )
;

return $config;
