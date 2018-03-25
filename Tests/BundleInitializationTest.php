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

namespace Ekino\NewRelicBundle\Tests;

use Ekino\NewRelicBundle\EkinoNewRelicBundle;
use Ekino\NewRelicBundle\NewRelic\BlackholeInteractor;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;

/**
 * Smoke test to see if the bundle can run.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BundleInitializationTest extends BaseBundleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Make services public that have an idea that matches a regex
        $this->addCompilerPass(new PublicServicePass('|ekino.new_relic.interactor.*|'));
    }

    protected function getBundleClass()
    {
        return EkinoNewRelicBundle::class;
    }

    public function testInitBundle()
    {
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        $services = [
            'ekino.new_relic.interactor.real' => NewRelicInteractor::class,
            'ekino.new_relic.interactor.blackhole' => BlackholeInteractor::class,
        ];

        // Test if you services exists
        foreach ($services as $id => $class) {
            $this->assertTrue($container->has($id));
            $service = $container->get($id);
            $this->assertInstanceOf($class, $service);
        }
    }
}
