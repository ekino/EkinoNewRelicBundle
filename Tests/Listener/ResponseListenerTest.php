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

use Ekino\NewRelicBundle\Listener\ResponseListener;
use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\Twig\NewRelicExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseListenerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $this->newRelic = $this->getMockBuilder(Config::class)
            ->setMethods(['getCustomEvents', 'getCustomMetrics', 'getCustomParameters'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = $this->getMockBuilder(NewRelicExtension::class)
            ->setMethods(['isHeaderCalled', 'isFooterCalled', 'isUsed'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOnKernelResponseOnlyMasterRequestsAreProcessed()
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = new Request();

        $eventClass = \class_exists(ResponseEvent::class) ? ResponseEvent::class : FilterResponseEvent::class;
        $event = new $eventClass($kernel, new Request(), HttpKernelInterface::SUB_REQUEST, new Response());

        $object = new ResponseListener($this->newRelic, $this->interactor);
        $object->onKernelResponse($event);

        $this->newRelic->expects($this->never())->method('getCustomMetrics');
    }

    public function testOnKernelResponseWithOnlyCustomMetricsAndParameters()
    {
        $events = [
            'WidgetSale' => [
                [
                    'color' => 'red',
                    'weight' => 12.5,
                ],
                [
                    'color' => 'blue',
                    'weight' => 12.5,
                ],
            ],
        ];

        $metrics = [
            'foo_a' => 4.7,
            'foo_b' => 11,
        ];

        $parameters = [
            'foo_1' => 'bar_1',
            'foo_2' => 'bar_2',
        ];

        $this->newRelic->expects($this->once())->method('getCustomEvents')->willReturn($events);
        $this->newRelic->expects($this->once())->method('getCustomMetrics')->willReturn($metrics);
        $this->newRelic->expects($this->once())->method('getCustomParameters')->willReturn($parameters);

        $this->interactor->expects($this->at(0))->method('addCustomMetric')->with('foo_a', 4.7);
        $this->interactor->expects($this->at(1))->method('addCustomMetric')->with('foo_b', 11);
        $this->interactor->expects($this->at(2))->method('addCustomParameter')->with('foo_1', 'bar_1');
        $this->interactor->expects($this->at(3))->method('addCustomParameter')->with('foo_2', 'bar_2');

        $this->interactor->expects($this->at(4))->method('addCustomEvent')->with('WidgetSale', [
            'color' => 'red',
            'weight' => 12.5,
        ]);
        $this->interactor->expects($this->at(5))->method('addCustomEvent')->with('WidgetSale', [
            'color' => 'blue',
            'weight' => 12.5,
        ]);

        $event = $this->createFilterResponseEventMock();

        $object = new ResponseListener($this->newRelic, $this->interactor, false);
        $object->onKernelResponse($event);
    }

    public function testOnKernelResponseInstrumentDisabledInRequest()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('disableAutoRUM');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, true);
        $object->onKernelResponse($event);
    }

    public function testSymfonyCacheEnabled()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('endTransaction');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, false, true);
        $object->onKernelResponse($event);
    }

    public function testSymfonyCacheDisabled()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->never())->method('endTransaction');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, false, false);
        $object->onKernelResponse($event);
    }

    /**
     * @dataProvider providerOnKernelResponseOnlyInstrumentHTMLResponses
     */
    public function testOnKernelResponseOnlyInstrumentHTMLResponses($content, $expectsSetContent, $contentType)
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('disableAutoRUM');
        $this->interactor->expects($this->any())->method('getBrowserTimingHeader')->willReturn('__Timing_Header__');
        $this->interactor->expects($this->any())->method('getBrowserTimingFooter')->willReturn('__Timing_Feader__');

        $request = $this->createRequestMock();
        $response = $this->createResponseMock($content, $expectsSetContent, $contentType);
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true);
        $object->onKernelResponse($event);
    }

    public function providerOnKernelResponseOnlyInstrumentHTMLResponses()
    {
        return [
            // unsupported content types
            [null, null, 'text/xml'],
            [null, null, 'text/plain'],
            [null, null, 'application/json'],

            ['content', 'content', 'text/html'],
            ['<div class="head">head</div>', '<div class="head">head</div>', 'text/html'],
            ['<header>content</header>', '<header>content</header>', 'text/html'],

            // head, body tags
            ['<head><title /></head>', '<head>__Timing_Header__<title /></head>', 'text/html'],
            ['<body><div /></body>', '<body><div />__Timing_Feader__</body>', 'text/html'],
            ['<head><title /></head><body><div /></body>', '<head>__Timing_Header__<title /></head><body><div />__Timing_Feader__</body>', 'text/html'],

            // with charset
            ['<head><title /></head><body><div /></body>', '<head>__Timing_Header__<title /></head><body><div />__Timing_Feader__</body>', 'text/html; charset=UTF-8'],
        ];
    }

    public function testInteractionWithTwigExtensionHeader()
    {
        $this->newRelic->expects($this->never())->method('getCustomMetrics');
        $this->newRelic->expects($this->never())->method('getCustomParameters');
        $this->newRelic->expects($this->once())->method('getCustomEvents')->willReturn([]);

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->never())->method('getBrowserTimingHeader');
        $this->interactor->expects($this->once())->method('getBrowserTimingFooter')->willReturn('__Timing_Feader__');

        $this->extension->expects($this->exactly(2))->method('isUsed')->willReturn(true);
        $this->extension->expects($this->once())->method('isHeaderCalled')->willReturn(true);
        $this->extension->expects($this->once())->method('isFooterCalled')->willReturn(false);

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onKernelResponse($event);
    }

    public function testInteractionWithTwigExtensionFooter()
    {
        $this->newRelic->expects($this->never())->method('getCustomMetrics');
        $this->newRelic->expects($this->never())->method('getCustomParameters');
        $this->newRelic->expects($this->once())->method('getCustomEvents')->willReturn([]);

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->once())->method('getBrowserTimingHeader')->willReturn('__Timing_Feader__');
        $this->interactor->expects($this->never())->method('getBrowserTimingFooter');

        $this->extension->expects($this->exactly(2))->method('isUsed')->willReturn(true);
        $this->extension->expects($this->once())->method('isHeaderCalled')->willReturn(false);
        $this->extension->expects($this->once())->method('isFooterCalled')->willReturn(true);

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onKernelResponse($event);
    }

    public function testInteractionWithTwigExtensionHeaderFooter()
    {
        $this->newRelic->expects($this->never())->method('getCustomMetrics');
        $this->newRelic->expects($this->never())->method('getCustomParameters');
        $this->newRelic->expects($this->once())->method('getCustomEvents')->willReturn([]);

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->never())->method('getBrowserTimingHeader');
        $this->interactor->expects($this->never())->method('getBrowserTimingFooter');

        $this->extension->expects($this->exactly(2))->method('isUsed')->willReturn(true);
        $this->extension->expects($this->once())->method('isHeaderCalled')->willReturn(true);
        $this->extension->expects($this->once())->method('isFooterCalled')->willReturn(true);

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onKernelResponse($event);
    }

    private function setUpNoCustomMetricsOrParameters()
    {
        $this->newRelic->expects($this->once())->method('getCustomEvents')->willReturn([]);
        $this->newRelic->expects($this->once())->method('getCustomMetrics')->willReturn([]);
        $this->newRelic->expects($this->once())->method('getCustomParameters')->willReturn([]);

        $this->interactor->expects($this->never())->method('addCustomEvent');
        $this->interactor->expects($this->never())->method('addCustomMetric');
        $this->interactor->expects($this->never())->method('addCustomParameter');
    }

    private function createRequestMock($instrumentEnabled = true)
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['get'])
            ->getMock();
        $mock->attributes = $mock;

        $mock->expects($this->any())->method('get')->willReturn($instrumentEnabled);

        return $mock;
    }

    private function createResponseMock($content = null, $expectsSetContent = null, $contentType = 'text/html')
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['get', 'getContent', 'setContent'])
            ->getMock();
        $mock->headers = $mock;

        $mock->expects($this->any())->method('get')->willReturn($contentType);
        $mock->expects($content ? $this->any() : $this->never())->method('getContent')->willReturn($content);

        if ($expectsSetContent) {
            $mock->expects($this->exactly(2))->method('setContent')->withConsecutive([''], [$expectsSetContent]);
        } else {
            $mock->expects($this->never())->method('setContent');
        }

        return $mock;
    }

    private function createFilterResponseEventMock($request = null, $response = null)
    {
        $event = $this->getMockBuilder(FilterResponseEvent::class)
            ->setMethods(['getResponse', 'getRequest', 'isMasterRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($request ? $this->any() : $this->never())->method('getRequest')->willReturn($request);
        $event->expects($response ? $this->any() : $this->never())->method('getResponse')->willReturn($response);
        $event->method('isMasterRequest')->willReturn(true);

        return $event;
    }
}
