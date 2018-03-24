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

use Ekino\Bundle\NewRelicBundle\Listener\ResponseListener;
use Ekino\Bundle\NewRelicBundle\NewRelic\Config;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\Twig\NewRelicExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListenerTest extends TestCase
{
    protected function setUp()
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

    public function testOnCoreResponseOnlyMasterRequestsAreProcessed()
    {
        $event = $this->getMockBuilder(FilterResponseEvent::class)
            ->setMethods(['isMasterRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('isMasterRequest')->will($this->returnValue(false));

        $object = new ResponseListener($this->newRelic, $this->interactor);
        $object->onCoreResponse($event);

        $this->newRelic->expects($this->never())->method('getCustomMetrics');
    }

    public function testOnCoreResponseWithOnlyCustomMetricsAndParameters()
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

        $this->newRelic->expects($this->once())->method('getCustomEvents')->will($this->returnValue($events));
        $this->newRelic->expects($this->once())->method('getCustomMetrics')->will($this->returnValue($metrics));
        $this->newRelic->expects($this->once())->method('getCustomParameters')->will($this->returnValue($parameters));

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
        $object->onCoreResponse($event);
    }

    public function testOnCoreResponseInstrumentDisabledInRequest()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('disableAutoRUM');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, true);
        $object->onCoreResponse($event);
    }

    public function testSymfonyCacheEnabled()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('endTransaction');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, false, true);
        $object->onCoreResponse($event);
    }

    public function testSymfonyCacheDisabled()
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->never())->method('endTransaction');

        $request = $this->createRequestMock(false);
        $event = $this->createFilterResponseEventMock($request, null);

        $object = new ResponseListener($this->newRelic, $this->interactor, false, false);
        $object->onCoreResponse($event);
    }

    /**
     * @dataProvider providerOnCoreResponseOnlyInstrumentHTMLResponses
     */
    public function testOnCoreResponseOnlyInstrumentHTMLResponses($content, $expectsSetContent, $contentType)
    {
        $this->setupNoCustomMetricsOrParameters();

        $this->interactor->expects($this->once())->method('disableAutoRUM');
        $this->interactor->expects($this->any())->method('getBrowserTimingHeader')->will($this->returnValue('__Timing_Header__'));
        $this->interactor->expects($this->any())->method('getBrowserTimingFooter')->will($this->returnValue('__Timing_Feader__'));

        $request = $this->createRequestMock();
        $response = $this->createResponseMock($content, $expectsSetContent, $contentType);
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true);
        $object->onCoreResponse($event);
    }

    public function providerOnCoreResponseOnlyInstrumentHTMLResponses()
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
        $this->newRelic->expects($this->once())->method('getCustomEvents')->will($this->returnValue([]));

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->never())->method('getBrowserTimingHeader');
        $this->interactor->expects($this->once())->method('getBrowserTimingFooter')->will($this->returnValue('__Timing_Feader__'));

        $this->extension->expects($this->exactly(2))->method('isUsed')->will($this->returnValue(true));
        $this->extension->expects($this->once())->method('isHeaderCalled')->will($this->returnValue(true));
        $this->extension->expects($this->once())->method('isFooterCalled')->will($this->returnValue(false));

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onCoreResponse($event);
    }

    public function testInteractionWithTwigExtensionFooter()
    {
        $this->newRelic->expects($this->never())->method('getCustomMetrics');
        $this->newRelic->expects($this->never())->method('getCustomParameters');
        $this->newRelic->expects($this->once())->method('getCustomEvents')->will($this->returnValue([]));

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->once())->method('getBrowserTimingHeader')->will($this->returnValue('__Timing_Feader__'));
        $this->interactor->expects($this->never())->method('getBrowserTimingFooter');

        $this->extension->expects($this->exactly(2))->method('isUsed')->will($this->returnValue(true));
        $this->extension->expects($this->once())->method('isHeaderCalled')->will($this->returnValue(false));
        $this->extension->expects($this->once())->method('isFooterCalled')->will($this->returnValue(true));

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onCoreResponse($event);
    }

    public function testInteractionWithTwigExtensionHeaderFooter()
    {
        $this->newRelic->expects($this->never())->method('getCustomMetrics');
        $this->newRelic->expects($this->never())->method('getCustomParameters');
        $this->newRelic->expects($this->once())->method('getCustomEvents')->will($this->returnValue([]));

        $this->interactor->expects($this->never())->method('disableAutoRUM');
        $this->interactor->expects($this->never())->method('getBrowserTimingHeader');
        $this->interactor->expects($this->never())->method('getBrowserTimingFooter');

        $this->extension->expects($this->exactly(2))->method('isUsed')->will($this->returnValue(true));
        $this->extension->expects($this->once())->method('isHeaderCalled')->will($this->returnValue(true));
        $this->extension->expects($this->once())->method('isFooterCalled')->will($this->returnValue(true));

        $request = $this->createRequestMock(true);
        $response = $this->createResponseMock('content', 'content', 'text/html');
        $event = $this->createFilterResponseEventMock($request, $response);

        $object = new ResponseListener($this->newRelic, $this->interactor, true, false, $this->extension);
        $object->onCoreResponse($event);
    }

    private function setUpNoCustomMetricsOrParameters()
    {
        $this->newRelic->expects($this->once())->method('getCustomEvents')->will($this->returnValue([]));
        $this->newRelic->expects($this->once())->method('getCustomMetrics')->will($this->returnValue([]));
        $this->newRelic->expects($this->once())->method('getCustomParameters')->will($this->returnValue([]));

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

        $mock->expects($this->any())->method('get')->will($this->returnValue($instrumentEnabled));

        return $mock;
    }

    private function createResponseMock($content = null, $expectsSetContent = null, $contentType = 'text/html')
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['get', 'getContent', 'setContent'])
            ->getMock();
        $mock->headers = $mock;

        $mock->expects($this->any())->method('get')->will($this->returnValue($contentType));
        $mock->expects($content ? $this->any() : $this->never())->method('getContent')->will($this->returnValue($content));

        if ($expectsSetContent) {
            $mock->expects($this->once())->method('setContent')->with($expectsSetContent);
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

        $event->expects($request ? $this->any() : $this->never())->method('getRequest')->will($this->returnValue($request));
        $event->expects($response ? $this->any() : $this->never())->method('getResponse')->will($this->returnValue($response));
        $event->method('isMasterRequest')->will($this->returnValue(true));

        return $event;
    }
}
