<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Listener\CommandListener;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->set(CommandListener::class)
            ->tag('kernel.event_subscriber')
    ;
};
