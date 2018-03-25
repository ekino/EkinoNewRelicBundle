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

namespace Ekino\Bundle\NewRelicBundle\Tests\Listener;

use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;
use Ekino\Bundle\NewRelicBundle\NewRelic\Config;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListenerTest extends TestCase
{
    public function testSubRequest()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('setTransactionName');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy);
        $listener->setApplicationName($event);
    }

    public function testMasterRequest()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('setTransactionName');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)
            ->setMethods(['getTransactionName'])
            ->getMock();
        $namingStrategy->expects($this->once())->method('getTransactionName')->will($this->returnValue('foobar'));

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy);
        $listener->setTransactionName($event);
    }

    public function testPathIsIgnored()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('ignoreTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/ignored_path']);

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], ['/ignored_path'], $namingStrategy);
        $listener->setIgnoreTransaction($event);
    }

    public function testRouteIsIgnored()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('ignoreTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request([], [], ['_route' => 'ignored_route']);

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, ['ignored_route'], [], $namingStrategy);
        $listener->setIgnoreTransaction($event);
    }

    public function testSymfonyCacheEnabled()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('startTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy, true);
        $listener->setApplicationName($event);
    }

    public function testSymfonyCacheDisabled()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('startTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy, false);
        $listener->setApplicationName($event);
    }
}
