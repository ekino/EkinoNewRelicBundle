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

namespace Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ControllerNamingStrategyTest extends TestCase
{
    public function testControllerAsString()
    {
        $request = new Request();
        $request->attributes->set('_controller', 'SomeBundle:Some:SomeAction');

        $strategy = new ControllerNamingStrategy();
        $this->assertSame('SomeBundle:Some:SomeAction', $strategy->getTransactionName($request));
    }

    public function testControllerAsClosure()
    {
        $request = new Request();
        $request->attributes->set('_controller', function () {
        });

        $strategy = new ControllerNamingStrategy();
        $this->assertSame('Closure controller', $strategy->getTransactionName($request));
    }

    public function testControllerAsCallback()
    {
        $request = new Request();
        $request->attributes->set('_controller', [$this, 'testControllerAsString']);

        $strategy = new ControllerNamingStrategy();
        $this->assertSame('Callback contoller: Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\ControllerNamingStrategyTest::testControllerAsString()', $strategy->getTransactionName($request));
    }

    public function testControllerUnknown()
    {
        $request = new Request();

        $strategy = new ControllerNamingStrategy();
        $this->assertSame('Unknown Symfony controller', $strategy->getTransactionName($request));
    }
}
