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

namespace Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ekino.new_relic.monolog.channels') || !$container->hasDefinition('monolog.logger')) {
            return;
        }

        $channels = $container->getParameter('ekino.new_relic.monolog.channels');
        foreach ($channels as $channel) {
            $def = $container->getDefinition('app' === $channel ? 'monolog.logger' : 'monolog.logger.'.$channel);
            $def->addMethodCall('pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
        }
    }
}
