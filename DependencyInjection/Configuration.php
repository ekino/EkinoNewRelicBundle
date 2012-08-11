<?php

/*
 * This file is part of the Sonata project.
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
                ->scalarNode('api_key')->defaultValue(false)->end()
                ->scalarNode('application_name')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('transaction_naming')
                    ->defaultValue('route')
                    ->validate()
                        ->ifNotInArray(array('route', 'controller'))
                        ->thenInvalid('Invalid transaction naming scheme "%s", must be "route" or "controller".')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
