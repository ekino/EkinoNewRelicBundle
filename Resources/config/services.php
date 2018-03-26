<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekino\NewRelicBundle\Command\NotifyDeploymentCommand;
use Ekino\NewRelicBundle\NewRelic\AdaptiveInteractor;
use Ekino\NewRelicBundle\NewRelic\BlackholeInteractor;
use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

return function (ContainerConfigurator $container) {

    $services = $container->services();

    $services
        ->defaults()
            ->private()
            ->autowire()

        ->load('Ekino\\NewRelicBundle\\NewRelic\\', './../../NewRelic/*')
        ->load('Ekino\\NewRelicBundle\\TransactionNamingStrategy\\', './../../TransactionNamingStrategy/*')

        ->set(AdaptiveInteractor::class)
            ->arg('$real', ref(NewRelicInteractor::class))
            ->arg('$fake', ref(BlackholeInteractor::class))

        // Default alias
        ->alias(NewRelicInteractorInterface::class, BlackholeInteractor::class)

        ->set(Config::class)
        ->set(LoggingInteractorDecorator::class)
            ->decorate(NewRelicInteractorInterface::class, 'ekino.logging_interactor_decorator.inner')
            ->arg('$interactor', ref('ekino.logging_interactor_decorator.inner'))

        ->set(NotifyDeploymentCommand::class)
            ->tag('console.command', ['command' => 'newrelic:notify-deployment'])
    ;
};
