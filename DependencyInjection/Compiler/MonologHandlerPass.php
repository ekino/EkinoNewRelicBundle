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

namespace Ekino\NewRelicBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class MonologHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ekino.new_relic.monolog') || !$container->hasDefinition('monolog.logger')) {
            return;
        }

        $configuration = $container->getParameter('ekino.new_relic.monolog');
        if ($container->hasDefinition('ekino.new_relic.logs_handler') && $container->hasParameter('ekino.new_relic.application_name')) {
            $container->findDefinition('ekino.new_relic.logs_handler')
                ->setArgument('$level', \is_int($configuration['level']) ? $configuration['level'] : \constant('Monolog\Logger::'.strtoupper($configuration['level'])))
                ->setArgument('$bubble', true)
                ->setArgument('$appName', $container->getParameter('ekino.new_relic.application_name'));
        }

        if (!isset($configuration['channels'])) {
            $channels = $this->getChannels($container);
        } elseif ('inclusive' === $configuration['channels']['type']) {
            $channels = $configuration['channels']['elements'] ?: $this->getChannels($container);
        } else {
            $channels = array_diff($this->getChannels($container), $configuration['channels']['elements']);
        }

        foreach ($channels as $channel) {
            try {
                $def = $container->getDefinition('app' === $channel ? 'monolog.logger' : 'monolog.logger.'.$channel);
            } catch (InvalidArgumentException $e) {
                $msg = 'NewRelicBundle configuration error: The logging channel "'.$channel.'" does not exist.';
                throw new \InvalidArgumentException($msg, 0, $e);
            }
            $def->addMethodCall('pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
        }
    }

    private function getChannels(ContainerBuilder $container)
    {
        $channels = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            if ('monolog.logger' === $id) {
                $channels[] = 'app';
                continue;
            }
            if (0 === strpos($id, 'monolog.logger.')) {
                $channels[] = substr($id, 15);
            }
        }

        return $channels;
    }
}
