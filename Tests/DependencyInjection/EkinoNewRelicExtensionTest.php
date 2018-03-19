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
}
