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

namespace Ekino\Bundle\NewRelicBundle\Tests\DependencyInjection;

use Ekino\Bundle\NewRelicBundle\DependencyInjection\EkinoNewRelicExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

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

        $this->assertContainerBuilderHasService('ekino.new_relic.twig.new_relic_extension');
        $this->assertContainerBuilderHasService('ekino.new_relic.command_listener');
        $this->assertContainerBuilderNotHasService('ekino.new_relic.exception_listener');
    }

    public function testAlternativeConfiguration()
    {
        $this->load([
            'log_exceptions' => true,
            'log_commands' => false,
            'twig' => false,
        ]);

        $this->assertContainerBuilderNotHasService('ekino.new_relic.twig.new_relic_extension');
        $this->assertContainerBuilderNotHasService('ekino.new_relic.command_listener');
        $this->assertContainerBuilderHasService('ekino.new_relic.exception_listener');
    }

    public function testDeprecation()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('ekino.new_relic.log_deprecations');
    }

    public function testLogs()
    {
        $this->load(['log_logs' => true]);

        $this->assertContainerBuilderHasParameter('ekino.new_relic.log_logs');
        $this->assertContainerBuilderHasService('ekino.new_relic.logs_handler');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('ekino.new_relic.logs_handler', 0, 400);
    }

    public function testConfigDisabled()
    {
        $this->load([
            'enabled' => false,
        ]);

        $this->assertContainerBuilderHasAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.blackhole');
    }

    public function testConfigDisabledWithInteractor()
    {
        $this->load([
            'enabled' => false,
            'interactor' => 'ekino.new_relic.interactor.auto',
        ]);

        $this->assertContainerBuilderHasAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.blackhole');
    }

    public function testConfigEnabledWithInteractor()
    {
        $this->load([
            'enabled' => true,
            'interactor' => 'ekino.new_relic.interactor.auto',
        ]);

        $this->assertContainerBuilderHasAlias('ekino.new_relic.interactor', 'ekino.new_relic.interactor.auto');
    }
}
