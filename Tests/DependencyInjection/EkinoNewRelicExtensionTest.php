<?php

declare(strict_types=1);

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

}
