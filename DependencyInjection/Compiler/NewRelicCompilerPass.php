<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class NewRelicCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (function_exists('newrelic_name_transaction')) {
            return;
        }

        $container->removeDefinition('ekino.new_relic.request_listener');
        $container->removeDefinition('ekino.new_relic.response_listener');
    }
}
