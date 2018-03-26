<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Handler\NewRelicHandler;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->load('Ekino\\NewRelicBundle\\Logging\\', './../../Logging/*')

        ->set('ekino.new_relic.monolog_handler', NewRelicHandler::class)
    ;
};
