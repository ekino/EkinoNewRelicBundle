<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Twig\NewRelicExtension;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->load('Ekino\\NewRelicBundle\\Twig\\', './../../Twig/*')

        ->set(NewRelicExtension::class)
            ->tag('twig.extension')
    ;
};
