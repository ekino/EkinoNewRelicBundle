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

namespace Ekino\NewRelicBundle\Tests\Listener;

use Ekino\NewRelicBundle\Listener\RequestListener;
use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListenerTest extends TestCase
{
    public function testSubRequest()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('setTransactionName');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, new Request(), HttpKernelInterface::SUB_REQUEST, new Response());

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
        $namingStrategy->expects($this->once())->method('getTransactionName')->willReturn('foobar');

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, new Request(), HttpKernelInterface::MASTER_REQUEST, new Response());

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

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, $request, HttpKernelInterface::MASTER_REQUEST, new Response());

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

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, $request, HttpKernelInterface::MASTER_REQUEST, new Response());

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, ['ignored_route'], [], $namingStrategy);
        $listener->setIgnoreTransaction($event);
    }

    public function testSymfonyCacheEnabled()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('startTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, new Request(), HttpKernelInterface::MASTER_REQUEST, new Response());

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy, true);
        $listener->setApplicationName($event);
    }

    public function testSymfonyCacheDisabled()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('startTransaction');

        $namingStrategy = $this->getMockBuilder(TransactionNamingStrategyInterface::class)->getMock();

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class;
        $event = new $eventClass($kernel, new Request(), HttpKernelInterface::MASTER_REQUEST, new Response());

        $listener = new RequestListener(new Config('App name', 'Token'), $interactor, [], [], $namingStrategy, false);
        $listener->setApplicationName($event);
    }
}
