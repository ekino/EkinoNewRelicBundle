<?php

namespace Ekino\Bundle\NewRelicBundle\Tests\DependencyInjection\Compiler;

use Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler\MonologHandlerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologHandlerPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MonologHandlerPass());
    }

    public function testProcessChannel()
    {
        $this->container->setParameter('ekino.new_relic.log_logs', ['channels' => ['app', 'foo']]);
        $this->registerService('monolog.logger', \Monolog\Logger::class);
        $this->registerService('monolog.logger.foo', \Monolog\Logger::class);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('monolog.logger.foo', 'pushHandler', [new Reference('ekino.new_relic.logs_handler')]);
    }
}
