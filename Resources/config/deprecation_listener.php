<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Listener\DeprecationListener;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->set(DeprecationListener::class)
            ->public()
    ;
};
