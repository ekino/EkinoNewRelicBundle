<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Twig;

use Ekino\Bundle\NewRelicBundle\Twig\NewRelicExtension;

class NewRelicExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic
     */
    private $newRelic;

    /**
     * @var \Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface
     */
    private $interactor;

    public function setUp()
    {
        $this->newRelic = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic', array('getCustomMetrics', 'getCustomParameters'), array(), '', false);
        $this->interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
    }

    /**
     * Tests the initial values returned by state methods
     */
    public function testInitialSetup()
    {
        $extension = new NewRelicExtension(
            $this->newRelic,
            $this->interactor
        );

        $this->assertFalse($extension->isHeaderCalled());
        $this->assertFalse($extension->isFooterCalled());
        $this->assertFalse($extension->isUsed());
    }

    public function testHeaderException()
    {
        $extension = new NewRelicExtension(
            $this->newRelic,
            $this->interactor
        );

        $this->newRelic->expects($this->once())
            ->method('getCustomMetrics')
            ->will($this->returnValue(array()));

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->will($this->returnValue(array()));

        $this->setExpectedException('\RuntimeException');

        $extension->getNewrelicBrowserTimingHeader();
        $extension->getNewrelicBrowserTimingHeader();
    }

    public function testFooterException()
    {
        $extension = new NewRelicExtension(
            $this->newRelic,
            $this->interactor
        );

        $this->newRelic->expects($this->once())
            ->method('getCustomMetrics')
            ->will($this->returnValue(array()));

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->will($this->returnValue(array()));

        $this->setExpectedException('\RuntimeException');

        $extension->getNewrelicBrowserTimingHeader();
        $extension->getNewrelicBrowserTimingHeader();
    }

    public function testPreparingOfInteractor()
    {
        $headerValue = '__HEADER__TIMING__';
        $footerValue = '__FOOTER__TIMING__';

        $extension = new NewRelicExtension(
            $this->newRelic,
            $this->interactor,
            true
        );

        $this->newRelic->expects($this->once())
            ->method('getCustomMetrics')
            ->will($this->returnValue(array(
                'a' => 'b',
                'c' => 'd',
            )));

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->will($this->returnValue(array(
                'e' => 'f',
                'g' => 'h',
                'i' => 'j',
            )));

        $this->interactor->expects($this->once())
            ->method('disableAutoRum');

        $this->interactor->expects($this->exactly(2))
            ->method('addCustomMetric');

        $this->interactor->expects($this->exactly(3))
            ->method('addCustomParameter');

        $this->interactor->expects($this->once())
            ->method('getNewrelicBrowserTimingHeader')
            ->will($this->returnValue($headerValue));

        $this->interactor->expects($this->once())
            ->method('getNewrelicBrowserTimingFooter')
            ->will($this->returnValue($footerValue));

        $this->assertEquals($headerValue, $extension->getNewrelicBrowserTimingHeader());
        $this->assertTrue($extension->isHeaderCalled());
        $this->assertFalse($extension->isFooterCalled());

        $this->assertEquals($footerValue, $extension->getNewrelicBrowserTimingFooter());
        $this->assertTrue($extension->isHeaderCalled());
        $this->assertTrue($extension->isFooterCalled());
    }
}
