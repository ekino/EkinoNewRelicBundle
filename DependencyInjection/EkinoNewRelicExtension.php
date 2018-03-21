<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Ekino\Bundle\NewRelicBundle\DependencyInjection;

use Monolog\Handler\NewRelicHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EkinoNewRelicExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->getDefinition('ekino.new_relic.request_listener')
            ->replaceArgument(2, $config['ignored_routes'])
            ->replaceArgument(3, $config['ignored_paths']);

        $container->getDefinition('ekino.new_relic.command_listener')
            ->replaceArgument(2, $config['ignored_commands']);

        $interactor = $config['enabled'] && extension_loaded('newrelic')
            ? 'ekino.new_relic.interactor.real'
            : 'ekino.new_relic.interactor.blackhole';

        if ($config['logging']) {
            $container->setAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.logger');
            $container->getDefinition('ekino.new_relic.interactor.logger')
                ->replaceArgument(0, new Reference($interactor));
        } else {
            $container->setAlias('ekino.new_relic.interactor', $interactor);
        }

        $container->getDefinition('ekino.new_relic.response_listener')
            ->replaceArgument(2, $config['instrument'])
            ->replaceArgument(3, $config['using_symfony_cache'])
        ;

        if (!$config['log_exceptions']) {
            $container->removeDefinition('ekino.new_relic.exception_listener');
        }

        $container->setParameter('ekino.new_relic.log_deprecations', $config['log_deprecations']);

        if (!$config['log_commands']) {
            $container->removeDefinition('ekino.new_relic.command_listener');
        }

        if (!$config['deployment_names']) {
            $config['deployment_names'] = array_values(array_filter(explode(';', $config['application_name'])));
        }

        $container->getDefinition('ekino.new_relic')
            ->replaceArgument(0, $config['application_name'])
            ->replaceArgument(1, $config['api_key'])
            ->replaceArgument(2, $config['license_key'])
            ->replaceArgument(3, $config['xmit'])
            ->replaceArgument(4, $config['deployment_names'])
        ;

        if (!$config['enabled']) {
            $config['log_logs']['enabled'] = false;
        }
        $container->setParameter('ekino.new_relic.log_logs', $config['log_logs']);
        if ($config['log_logs']['enabled']) {
            if (!class_exists(NewRelicHandler::class)) {
                throw new \LogicException('The "symfony/monolog-bundle" package must be installed in order to use "log_logs" option.');
            }

            $container->setAlias('ekino.new_relic.logs_handler', $config['log_logs']['service']);

            $level = $config['log_logs']['level'];
            $container->findDefinition('ekino.new_relic.logs_handler')
                ->replaceArgument(0, is_int($level) ? $level : constant('Monolog\Logger::'.strtoupper($level)))
                ->replaceArgument(2, $config['application_name']);
        }

        switch ($config['transaction_naming']) {
            case 'controller':
                $transaction_naming_service = new Reference('ekino.new_relic.transaction_naming_strategy.controller');
                break;
            case 'route':
                $transaction_naming_service = new Reference('ekino.new_relic.transaction_naming_strategy.route');
                break;
            case 'service':
                if (!isset($config['transaction_naming_service']))
                {
                    throw new \LogicException('When using the "service", transaction naming scheme, the "transaction_naming_service" config parameter must be set.');
                }

                $transaction_naming_service = new Reference($config['transaction_naming_service']);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid transaction naming scheme "%s", must be "route", "controller" or "service".', $config['transaction_naming']));
        }

        $container->getDefinition('ekino.new_relic.request_listener')
            ->replaceArgument(4, $transaction_naming_service)
            ->replaceArgument(5, $config['using_symfony_cache'])
        ;

        if (!$config['twig']) {
            $container->removeDefinition('ekino.new_relic.twig.new_relic_extension');
        }
    }
}
