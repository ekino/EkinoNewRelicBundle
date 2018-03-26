<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Listener\ExceptionListener;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->set(ExceptionListener::class)
            ->tag('kernel.event_subscriber')
    ;
};
