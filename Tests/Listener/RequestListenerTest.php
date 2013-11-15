<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Listener;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSubRequest()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->never())->method('setTransactionName');

        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, $namingStrategy);
        $listener->onCoreRequest($event);
    }

    public function testMasterRequest()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('setTransactionName');

        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, $namingStrategy);
        $listener->onCoreRequest($event);
    }
}
