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

namespace Ekino\NewRelicBundle\Tests\DependencyInjection\Compiler;

use Ekino\NewRelicBundle\DependencyInjection\Compiler\MonologHandlerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologHandlerPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MonologHandlerPass());
    }

    public function testProcessChannel()
    {
        $this->container->setParameter('ekino.new_relic.monolog', ['level' => 100, 'channels' => ['type' => 'inclusive', 'elements' => ['app', 'foo']]]);
        $this->container->setParameter('ekino.new_relic.application_name', 'app');
        $this->registerService('ekino.new_relic.monolog_handler', \Monolog\Handler\NewRelicHandler::class);
        $this->container->setAlias('ekino.new_relic.logs_handler', 'ekino.new_relic.monolog_handler')->setPublic(false);
        $this->registerService('monolog.logger', \Monolog\Logger::class)->setArgument(0, 'app');
        $this->registerService('monolog.logger.foo', \Monolog\Logger::class)->setArgument(0, 'foo');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger.foo', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
    }

    public function testProcessChannelAllChannels()
    {
        $this->container->setParameter('ekino.new_relic.monolog', ['level' => 100, 'channels' => null]);
        $this->container->setParameter('ekino.new_relic.application_name', 'app');
        $this->registerService('ekino.new_relic.monolog_handler', \Monolog\Handler\NewRelicHandler::class);
        $this->container->setAlias('ekino.new_relic.logs_handler', 'ekino.new_relic.monolog_handler')->setPublic(false);
        $this->registerService('monolog.logger', \Monolog\Logger::class)->setArgument(0, 'app');
        $this->registerService('monolog.logger.foo', \Monolog\Logger::class)->setArgument(0, 'foo');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger.foo', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
    }

    public function testProcessChannelExcludeChannels()
    {
        $this->container->setParameter('ekino.new_relic.monolog', ['level' => 100, 'channels' => ['type' => 'exclusive', 'elements' => ['foo']]]);
        $this->container->setParameter('ekino.new_relic.application_name', 'app');
        $this->registerService('ekino.new_relic.monolog_handler', \Monolog\Handler\NewRelicHandler::class);
        $this->container->setAlias('ekino.new_relic.logs_handler', 'ekino.new_relic.monolog_handler')->setPublic(false);
        $this->registerService('monolog.logger', \Monolog\Logger::class)->setArgument(0, 'app');
        $this->registerService('monolog.logger.foo', \Monolog\Logger::class)->setArgument(0, 'foo');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
    }
}
