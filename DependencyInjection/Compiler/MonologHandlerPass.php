<?php

namespace Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ekino.new_relic.log_logs') || !$container->hasDefinition('monolog.logger')) {
            return;
        }

        $config = $container->getParameter('ekino.new_relic.log_logs');
        foreach ($config['channels'] as $channel) {
            $def = $container->getDefinition($channel === 'app' ? 'monolog.logger' : 'monolog.logger.'.$channel);
            $def->addMethodCall('pushHandler', array(new Reference('ekino.new_relic.logs_handler')));
        }
    }
}
