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

namespace Ekino\NewRelicBundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Twig\Extension;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ekino_new_relic');
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('ekino_new_relic');
        }

        $rootNode
            ->fixXmlConfig('deployment_name')
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('interactor')->end()
                ->booleanNode('twig')->defaultValue(\class_exists(Extension::class))->end()
                ->scalarNode('api_key')->defaultValue(null)->end()
                ->scalarNode('license_key')->defaultValue(null)->end()
                ->scalarNode('application_name')->defaultValue(null)->end()
                ->arrayNode('deployment_names')
                    ->prototype('scalar')
                    ->end()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return !\is_array($v); })
                        ->then(function ($v) { return \array_values(\array_filter(\explode(';', (string) $v))); })
                    ->end()
                ->end()
                ->scalarNode('xmit')->defaultValue(false)->end()
                ->booleanNode('logging')
                    ->info('Write logs to a PSR3 logger whenever we send data to NewRelic.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('exceptions')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('commands')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('ignored_commands')
                            ->prototype('scalar')
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return !\is_array($v); })
                                ->then(function ($v) { return (array) $v; })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('deprecations')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('http')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('transaction_naming')
                            ->defaultValue('route')
                            ->validate()
                                ->ifNotInArray(['route', 'controller', 'service'])
                                ->thenInvalid('Invalid transaction naming scheme "%s", must be "route", "controller" or "service".')
                            ->end()
                        ->end()
                        ->scalarNode('transaction_naming_service')->defaultNull()->end()
                        ->arrayNode('ignored_routes')
                            ->prototype('scalar')
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return !\is_array($v); })
                                ->then(function ($v) { return (array) $v; })
                            ->end()
                        ->end()
                        ->arrayNode('ignored_paths')
                            ->prototype('scalar')
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return !\is_array($v); })
                                ->then(function ($v) { return (array) $v; })
                            ->end()
                        ->end()
                        ->scalarNode('using_symfony_cache')->defaultFalse()->end()
                    ->end()
                ->end()
                ->booleanNode('instrument')
                    ->defaultFalse()
                ->end()
                ->arrayNode('monolog')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('channels')
                            ->fixXmlConfig('channel', 'elements')
                            ->canBeUnset()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return ['elements' => [$v]]; })
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return \is_array($v) && \is_numeric(\key($v)); })
                                ->then(function ($v) { return ['elements' => $v]; })
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) { return empty($v); })
                                ->thenUnset()
                            ->end()
                            ->validate()
                                ->always(function ($v) {
                                    $isExclusive = null;
                                    if (isset($v['type'])) {
                                        $isExclusive = 'exclusive' === $v['type'];
                                    }

                                    $elements = [];
                                    foreach ($v['elements'] as $element) {
                                        if (0 === \strpos($element, '!')) {
                                            if (false === $isExclusive) {
                                                throw new InvalidConfigurationException('Cannot combine exclusive/inclusive definitions in channels list.');
                                            }
                                            $elements[] = \substr($element, 1);
                                            $isExclusive = true;
                                        } else {
                                            if (true === $isExclusive) {
                                                throw new InvalidConfigurationException('Cannot combine exclusive/inclusive definitions in channels list');
                                            }
                                            $elements[] = $element;
                                            $isExclusive = false;
                                        }
                                    }

                                    if (!\count($elements)) {
                                        return;
                                    }

                                    return ['type' => $isExclusive ? 'exclusive' : 'inclusive', 'elements' => $elements];
                                })
                            ->end()
                                ->children()
                                ->scalarNode('type')
                                    ->validate()
                                        ->ifNotInArray(['inclusive', 'exclusive'])
                                        ->thenInvalid('The type of channels has to be inclusive or exclusive')
                                    ->end()
                                ->end()
                                ->arrayNode('elements')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('level')->defaultValue(LogLevel::ERROR)->end()
                        ->scalarNode('service')->defaultValue('ekino.new_relic.monolog_handler')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
