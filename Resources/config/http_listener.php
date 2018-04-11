<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Listener\RequestListener;
use Ekino\NewRelicBundle\Listener\ResponseListener;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->set(RequestListener::class)
            ->tag('kernel.event_subscriber')

        ->set(ResponseListener::class)
            ->tag('kernel.event_subscriber')
    ;
};
