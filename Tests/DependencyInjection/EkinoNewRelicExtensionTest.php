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

namespace Ekino\NewRelicBundle\Tests\DependencyInjection;

use Ekino\NewRelicBundle\DependencyInjection\EkinoNewRelicExtension;
use Ekino\NewRelicBundle\Listener\CommandListener;
use Ekino\NewRelicBundle\Listener\DeprecationListener;
use Ekino\NewRelicBundle\Listener\ExceptionListener;
use Ekino\NewRelicBundle\NewRelic\BlackholeInteractor;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\Twig\NewRelicExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerHasParameterConstraint;
use PHPUnit\Framework\Constraint\LogicalNot;

class EkinoNewRelicExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [new EkinoNewRelicExtension()];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->setParameter('kernel.bundles', []);
    }

    public function testDefaultConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderHasService(NewRelicExtension::class);
        $this->assertContainerBuilderHasService(CommandListener::class);
        $this->assertContainerBuilderHasService(ExceptionListener::class);
    }

    public function testAlternativeConfiguration()
    {
        $this->load([
            'exceptions' => false,
            'commands' => false,
            'twig' => false,
        ]);

        $this->assertContainerBuilderNotHasService(NewRelicExtension::class);
        $this->assertContainerBuilderNotHasService(CommandListener::class);
        $this->assertContainerBuilderNotHasService(ExceptionListener::class);
    }

    public function testDeprecation()
    {
        $this->load();

        $this->assertContainerBuilderHasService(DeprecationListener::class);
    }

    public function testMonolog()
    {
        $this->load(['monolog' => true]);

        $this->assertContainerBuilderHasParameter('ekino.new_relic.monolog.channels');
        $this->assertContainerBuilderHasService('ekino.new_relic.logs_handler');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('ekino.new_relic.logs_handler', '$level', 400);
    }

    public function testMonologDisabled()
    {
        $this->load(['monolog' => false]);

        self::assertThat(
            $this->container,
            new LogicalNot(new ContainerHasParameterConstraint('ekino.new_relic.monolog.channels', null, false))
        );
    }

    public function testConfigDisabled()
    {
        $this->load([
            'enabled' => false,
        ]);

        $this->assertContainerBuilderHasAlias(NewRelicInteractorInterface::class, BlackholeInteractor::class);
    }

    public function testConfigDisabledWithInteractor()
    {
        $this->load([
            'enabled' => false,
            'interactor' => 'ekino.new_relic.interactor.adaptive',
        ]);

        $this->assertContainerBuilderHasAlias(NewRelicInteractorInterface::class, BlackholeInteractor::class);
    }

    public function testConfigEnabledWithInteractor()
    {
        $this->load([
            'enabled' => true,
            'interactor' => 'ekino.new_relic.interactor.adaptive',
        ]);

        $this->assertContainerBuilderHasAlias(NewRelicInteractorInterface::class, 'ekino.new_relic.interactor.adaptive');
    }
}
