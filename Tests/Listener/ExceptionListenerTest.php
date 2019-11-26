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

use Ekino\NewRelicBundle\Listener\ExceptionListener;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    public function testOnKernelException()
    {
        $exception = new \Exception('Boom');

        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable')->with($exception);

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $eventClass = \class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass($kernel, $request, HttpKernelInterface::SUB_REQUEST, $exception);

        $listener = new ExceptionListener($interactor);
        $listener->onKernelException($event);
    }

    public function testOnKernelExceptionWithHttp()
    {
        $exception = new BadRequestHttpException('Boom');

        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('noticeThrowable');

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $eventClass = \class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass($kernel, $request, HttpKernelInterface::SUB_REQUEST, $exception);

        $listener = new ExceptionListener($interactor);
        $listener->onKernelException($event);
    }
}
