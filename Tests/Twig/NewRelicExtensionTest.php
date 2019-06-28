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

namespace Ekino\NewRelicBundle\Tests\Twig;

use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\Twig\NewRelicExtension;
use PHPUnit\Framework\TestCase;

class NewRelicExtensionTest extends TestCase
{
    /**
     * @var \Ekino\NewRelicBundle\NewRelic\Config
     */
    private $newRelic;

    /**
     * @var \Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface
     */
    private $interactor;

    protected function setUp()
    {
        $this->newRelic = $this->getMockBuilder(Config::class)
        ->setMethods(['getCustomMetrics', 'getCustomParameters'])
        ->disableOriginalConstructor()
            ->getMock();
        $this->interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
    }

    /**
     * Tests the initial values returned by state methods.
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
            ->willReturn([]);

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->willReturn([]);

        $this->expectException(\RuntimeException::class);

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
            ->willReturn([]);

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->willReturn([]);

        $this->expectException(\RuntimeException::class);

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
            ->willReturn([
                'a' => 'b',
                'c' => 'd',
            ]);

        $this->newRelic->expects($this->once())
            ->method('getCustomParameters')
            ->willReturn([
                'e' => 'f',
                'g' => 'h',
                'i' => 'j',
            ]);

        $this->interactor->expects($this->once())
            ->method('disableAutoRum');

        $this->interactor->expects($this->exactly(2))
            ->method('addCustomMetric');

        $this->interactor->expects($this->exactly(3))
            ->method('addCustomParameter');

        $this->interactor->expects($this->once())
            ->method('getBrowserTimingHeader')
            ->willReturn($headerValue);

        $this->interactor->expects($this->once())
            ->method('getBrowserTimingFooter')
            ->willReturn($footerValue);

        $this->assertSame($headerValue, $extension->getNewrelicBrowserTimingHeader());
        $this->assertTrue($extension->isHeaderCalled());
        $this->assertFalse($extension->isFooterCalled());

        $this->assertSame($footerValue, $extension->getNewrelicBrowserTimingFooter());
        $this->assertTrue($extension->isHeaderCalled());
        $this->assertTrue($extension->isFooterCalled());
    }
}
