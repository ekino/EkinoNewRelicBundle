<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Ekino\NewRelicBundle\DependencyInjection;

use Ekino\NewRelicBundle\Listener\CommandListener;
use Ekino\NewRelicBundle\Listener\RequestListener;
use Ekino\NewRelicBundle\Listener\ResponseListener;
use Ekino\NewRelicBundle\NewRelic\AdaptiveInteractor;
use Ekino\NewRelicBundle\NewRelic\BlackholeInteractor;
use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\TransactionNamingStrategy\ControllerNamingStrategy;
use Ekino\NewRelicBundle\TransactionNamingStrategy\RouteNamingStrategy;
use Ekino\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EkinoNewRelicExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setAlias(NewRelicInteractorInterface::class, $this->getInteractorServiceId($config))->setPublic(false);
        $container->setAlias(TransactionNamingStrategyInterface::class, $this->getTransactionNamingServiceId($config))->setPublic(false);

        if ($config['logging']) {
            $container->register(LoggingInteractorDecorator::class)
                ->setDecoratedService(NewRelicInteractorInterface::class)
                ->setArguments(
                    [
                        '$interactor' => new Reference(LoggingInteractorDecorator::class.'.inner'),
                        '$logger' => new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                    ]
                )
                ->setPublic(false)
            ;
        }

        if (empty($config['deployment_names'])) {
            $config['deployment_names'] = \array_values(\array_filter(\explode(';', $config['application_name'] ?? '')));
        }

        $container->getDefinition(Config::class)
            ->setArguments(
                [
                    '$name' => $config['application_name'],
                    '$apiKey' => $config['api_key'],
                    '$licenseKey' => $config['license_key'],
                    '$xmit' => $config['xmit'],
                    '$deploymentNames' => $config['deployment_names'],
                ]
            );

        if ($config['http']['enabled']) {
            $loader->load('http_listener.xml');
            $container->getDefinition(RequestListener::class)
                ->setArguments(
                    [
                        '$ignoreRoutes' => $config['http']['ignored_routes'],
                        '$ignoredPaths' => $config['http']['ignored_paths'],
                        '$symfonyCache' => $config['http']['using_symfony_cache'],
                    ]
                );

            $container->getDefinition(ResponseListener::class)
                ->setArguments(
                    [
                        '$instrument' => $config['instrument'],
                        '$symfonyCache' => $config['http']['using_symfony_cache'],
                    ]
                );
        }

        if ($config['commands']['enabled']) {
            $loader->load('command_listener.xml');
            $container->getDefinition(CommandListener::class)
                ->setArguments(
                    [
                        '$ignoredCommands' => $config['commands']['ignored_commands'],
                    ]
                );
        }

        if ($config['exceptions']['enabled']) {
            $loader->load('exception_listener.xml');
        }

        if ($config['deprecations']['enabled']) {
            $loader->load('deprecation_listener.xml');
        }

        if ($config['twig']) {
            $loader->load('twig.xml');
        }

        if ($config['enabled'] && $config['monolog']['enabled']) {
            if (!\class_exists(\Monolog\Handler\NewRelicHandler::class)) {
                throw new \LogicException('The "symfony/monolog-bundle" package must be installed in order to use "monolog" option.');
            }
            $loader->load('monolog.xml');
            $container->setParameter('ekino.new_relic.monolog.channels', $config['monolog']['channels']);
            $container->setAlias('ekino.new_relic.logs_handler', $config['monolog']['service'])->setPublic(false);

            $level = $config['monolog']['level'];
            // This service is used by MonologHandlerPass to inject into Monolog Service
            $container->findDefinition('ekino.new_relic.logs_handler')
                ->setArguments(
                    [
                        '$level' => \is_int($level) ? $level : \constant('Monolog\Logger::'.\strtoupper($level)),
                        '$appName' => $config['application_name'],
                    ]
                );
        }
    }

    private function getInteractorServiceId(array $config): string
    {
        if (!$config['enabled']) {
            return BlackholeInteractor::class;
        }

        if (!isset($config['interactor'])) {
            // Fallback on AdaptiveInteractor.
            return AdaptiveInteractor::class;
        }

        if ('auto' === $config['interactor']) {
            // Check if the extension is loaded or not
            return \extension_loaded('newrelic') ? NewRelicInteractor::class : BlackholeInteractor::class;
        }

        return $config['interactor'];
    }

    private function getTransactionNamingServiceId(array $config): string
    {
        switch ($config['http']['transaction_naming']) {
            case 'controller':
                return ControllerNamingStrategy::class;
            case 'route':
                return RouteNamingStrategy::class;
            case 'service':
                if (!isset($config['http']['transaction_naming_service'])) {
                    throw new \LogicException(
                        'When using the "service", transaction naming scheme, the "transaction_naming_service" config parameter must be set.'
                    );
                }

                return $config['http']['transaction_naming_service'];
            default:
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Invalid transaction naming scheme "%s", must be "route", "controller" or "service".',
                        $config['http']['transaction_naming']
                    )
                );
        }
    }
}
