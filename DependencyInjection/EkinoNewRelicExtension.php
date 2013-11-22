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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

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

        if ($config['transaction_naming'] =='service' && !isset($config['transaction_naming_service'])) {
            throw new \LogicException('When using the "service", transaction naming scheme, the "transaction_naming_service" config parameter must be set.');
        };

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('ekino.new_relic.configuration.application_name', 'application_name');
        $container->setParameter('ekino.new_relic.configuration.api_key', 'api_key');
        $container->setParameter('ekino.new_relic.configuration.license_key', 'license_key');
        $container->setParameter('ekino.new_relic.configuration.xmit', 'xmit');
        $container->setParameter('ekino.new_relic.configuration.instrument', 'instrument');

        $container->setAlias('ekino.new_relic.transaction_naming_strategy', 'ekino.new_relic.transaction_naming_strategy.' . $config['transaction_naming']);

        $interactor = $container->getDefinition('ekino.new_relic.interactor');
        if ($config['enabled'] && function_exists('newrelic_name_transaction')) {
            $interactor->addMethodCall('addInteractor', array(new Reference('ekino.new_relic.interactor.newrelic')));
        }

        if ($config['logging']) {
            $interactor->addMethodCall('addInteractor', array(new Reference('ekino.new_relic.interactor.real')));
        }

        if (!$config['log_exceptions']) {
            $container->removeDefinition('ekino.new_relic.exception_listener');
        }

        if (!$config['log_commands']) {
            $container->removeDefinition('ekino.new_relic.command_listener');
        }
    }
}
