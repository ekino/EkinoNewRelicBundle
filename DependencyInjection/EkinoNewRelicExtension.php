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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;

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

        if (!$config['enabled']) {
            return;
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['logging'])
        {
            $container->setAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.logger');
        }
        else
        {
            $container->setAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.real');
        }

        $container->getDefinition('ekino.new_relic.response_listener')
            ->replaceArgument(2, $config['instrument']);
        ;

        if (!$config['log_exceptions'])
        {
            $container->removeDefinition('ekino.new_relic.exception_listener');
        }

        if (!$config['log_commands']) {
            $container->removeDefinition('ekino.new_relic.command_listener');
        }

        $container->getDefinition('ekino.new_relic')
            ->replaceArgument(0, $config['application_name'])
            ->replaceArgument(1, $config['api_key'])
            ->replaceArgument(2, $config['license_key'])
            ->replaceArgument(3, $config['xmit'])
        ;

        switch ($config['transaction_naming'])
        {
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
        ;
    }
}
