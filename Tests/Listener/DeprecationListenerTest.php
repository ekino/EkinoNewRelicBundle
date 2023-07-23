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

use Ekino\NewRelicBundle\Exception\DeprecationException;
use Ekino\NewRelicBundle\Listener\DeprecationListener;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\TestCase;

class DeprecationListenerTest extends TestCase
{
    public function testDeprecationIsReported()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable')->with(
            $this->isInstanceOf(DeprecationException::class)
        );

        $listener = new DeprecationListener($interactor);

        set_error_handler(function () { return false; });
        try {
            $listener->register();
            @trigger_error('This is a deprecation', \E_USER_DEPRECATED);
        } finally {
            $listener->unregister();
            restore_error_handler();
        }
    }

    public function testDeprecationIsReportedRegardlessErrorReporting()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable');

        $listener = new DeprecationListener($interactor);

        set_error_handler(function () { return false; });
        $e = error_reporting(0);
        try {
            $listener->register();
            @trigger_error('This is a deprecation', \E_USER_DEPRECATED);
        } finally {
            $listener->unregister();
            error_reporting($e);
            restore_error_handler();
        }
    }

    public function testOtherErrorAreIgnored()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('noticeThrowable');

        $listener = new DeprecationListener($interactor);

        set_error_handler(function () { return false; });
        try {
            $listener->register();
            @trigger_error('This is a notice');
        } finally {
            $listener->unregister();
            restore_error_handler();
        }
    }

    public function testInitialHandlerIsCalled()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable');

        $handler = $this->createPartialMock(DummyHandler::class, ['__invoke']);
        $handler->expects($this->once())->method('__invoke');

        $listener = new DeprecationListener($interactor);

        set_error_handler($handler);
        try {
            $listener->register();
            @trigger_error('This is a deprecation', \E_USER_DEPRECATED);
        } finally {
            $listener->unregister();
            restore_error_handler();
        }
    }

    public function testUnregisterRemovesHandler()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('noticeThrowable');

        $listener = new DeprecationListener($interactor);

        set_error_handler(function () { return false; });
        try {
            $listener->register();
            $listener->unregister();
            @trigger_error('This is a deprecation', \E_USER_DEPRECATED);
        } finally {
            restore_error_handler();
        }
    }

    public function testUnregisterRestorePreviousHandler()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();

        $handler = $this->createPartialMock(DummyHandler::class, ['__invoke']);
        $handler->expects($this->once())->method('__invoke');

        $listener = new DeprecationListener($interactor);

        set_error_handler($handler);
        try {
            $listener->register();
            $listener->unregister();
            @trigger_error('This is a deprecation', \E_USER_DEPRECATED);
        } finally {
            restore_error_handler();
        }
    }
}

class DummyHandler
{
    public function __invoke()
    {
    }
}
