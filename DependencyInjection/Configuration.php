<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekino_new_relic');

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('api_key')->defaultValue(false)->end()
                ->scalarNode('license_key')->defaultValue(null)->end()
                ->scalarNode('application_name')->defaultValue(null)->end()
                ->scalarNode('xmit')->defaultValue(false)->end()
                ->scalarNode('logging')
                    ->defaultFalse()
                    ->validate()
                        ->ifNotInArray(array(true, false))
                        ->thenInvalid('The logging parameter must be true or false')
                    ->end()
                ->end()
                ->scalarNode('instrument')
                    ->defaultFalse()
                    ->validate()
                        ->ifNotInArray(array(true, false))
                        ->thenInvalid('The instrument parameter must be true or false')
                    ->end()
                ->end()
                ->scalarNode('log_exceptions')
                    ->defaultFalse()
                    ->validate()
                        ->ifNotInArray(array(true, false))
                        ->thenInvalid('The log_exceptions parameter must be true or false')
                    ->end()
                ->end()
                ->scalarNode('log_commands')
                    ->defaultTrue()
                    ->validate()
                        ->ifNotInArray(array(true, false))
                        ->thenInvalid('The log_commands parameter must be true or false')
                    ->end()
                ->end()
                ->scalarNode('transaction_naming')
                    ->defaultValue('route')
                    ->validate()
                        ->ifNotInArray(array('route', 'controller', 'service'))
                        ->thenInvalid('Invalid transaction naming scheme "%s", must be "route", "controller" or "service".')
                    ->end()
                ->end()
                ->scalarNode('transaction_naming_service')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
