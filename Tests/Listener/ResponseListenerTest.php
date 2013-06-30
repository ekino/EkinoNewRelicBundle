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

class ResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->newRelic = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic', array('getCustomMetrics', 'getCustomParameters'), array(), '', false);
        $this->interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
    }

    public function testOnCoreResponseWithOnlyCustomMetricsAndParameters()
    {
        $metrics = array(
            'foo_a' => 'bar_a',
            'foo_b' => 'bar_b',
        );

        $parameters = array(
            'foo_1' => 'bar_1',
            'foo_2' => 'bar_2',
        );

        $this->newRelic->expects($this->once())->method('getCustomMetrics')->will($this->returnValue($metrics));
        $this->newRelic->expects($this->once())->method('getCustomParameters')->will($this->returnValue($parameters));

        $this->interactor->expects($this->at(0))->method('addCustomMetric')->with('foo_a', 'bar_a');
        $this->interactor->expects($this->at(1))->method('addCustomMetric')->with('foo_b', 'bar_b');
        $this->interactor->expects($this->at(2))->method('addCustomParameter')->with('foo_1', 'bar_1');
        $this->interactor->expects($this->at(3))->method('addCustomParameter')->with('foo_2', 'bar_2');

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
        return array(
            // unsupported content types
            array(null, null, 'text/xml'),
            array(null, null, 'text/plain'),
            array(null, null, 'application/json'),

            array('content', 'content', 'text/html'),
            array('<div class="head">head</div>', '<div class="head">head</div>', 'text/html'),
            array('<header>content</header>', '<header>content</header>', 'text/html'),

            // head, body tags
            array('<head><title /></head>', '<head>__Timing_Header__<title /></head>', 'text/html'),
            array('<body><div /></body>', '<body><div />__Timing_Feader__</body>', 'text/html'),
            array('<head><title /></head><body><div /></body>', '<head>__Timing_Header__<title /></head><body><div />__Timing_Feader__</body>', 'text/html'),

            // with charset
            array('<head><title /></head><body><div /></body>', '<head>__Timing_Header__<title /></head><body><div />__Timing_Feader__</body>', 'text/html; charset=UTF-8'),
        );
    }

    private function setUpNoCustomMetricsOrParameters()
    {
        $this->newRelic->expects($this->once())->method('getCustomMetrics')->will($this->returnValue(array()));
        $this->newRelic->expects($this->once())->method('getCustomParameters')->will($this->returnValue(array()));

        $this->interactor->expects($this->never())->method('addCustomMetric');
        $this->interactor->expects($this->never())->method('addCustomParameter');
    }

    private function createRequestMock($instrumentEnabled = true)
    {
        $mock = $this->getMock('stdClass', array('get'));
        $mock->attributes = $mock;

        $mock->expects($this->any())->method('get')->will($this->returnValue($instrumentEnabled));

        return $mock;
    }

    private function createResponseMock($content = null, $expectsSetContent = null, $contentType = 'text/html')
    {
        $mock = $this->getMock('stdClass', array('get', 'getContent', 'setContent'));
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
        $event = $this->getMock('Symfony\Component\HttpKernel\Event\FilterResponseEvent', array('getResponse', 'getRequest'), array(), '', false);

        $event->expects($request ? $this->any() : $this->never())->method('getRequest')->will($this->returnValue($request));
        $event->expects($response ? $this->any() : $this->never())->method('getResponse')->will($this->returnValue($response));

        return $event;
    }
}
