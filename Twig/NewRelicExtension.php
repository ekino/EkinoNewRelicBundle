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

namespace Ekino\Bundle\NewRelicBundle\Twig;

use Ekino\Bundle\NewRelicBundle\NewRelic\Config;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

/**
 * Twig extension to manually include BrowserTimingHeader and BrowserTimingFooter into twig templates.
 */
class NewRelicExtension extends \Twig_Extension
{
    private $newRelic;
    private $interactor;
    private $instrument;
    private $headerCalled = false;
    private $footerCalled = false;

    public function __construct(
        Config $newRelic,
        NewRelicInteractorInterface $interactor,
        bool $instrument = false
    ) {
        $this->newRelic = $newRelic;
        $this->interactor = $interactor;
        $this->instrument = $instrument;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('ekino_newrelic_browser_timing_header', [$this, 'getNewrelicBrowserTimingHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ekino_newrelic_browser_timing_footer', [$this, 'getNewrelicBrowserTimingFooter'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getNewrelicBrowserTimingHeader(): string
    {
        if ($this->isHeaderCalled()) {
            throw new \RuntimeException('Function "ekino_newrelic_browser_timing_header" has already been called');
        }

        $this->prepareInteractor();

        $this->headerCalled = true;

        return $this->interactor->getBrowserTimingHeader();
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getNewrelicBrowserTimingFooter(): string
    {
        if ($this->isFooterCalled()) {
            throw new \RuntimeException('Function "ekino_newrelic_browser_timing_footer" has already been called');
        }

        if (false === $this->isHeaderCalled()) {
            $this->prepareInteractor();
        }

        $this->footerCalled = true;

        return $this->interactor->getBrowserTimingFooter();
    }

    public function isHeaderCalled(): bool
    {
        return $this->headerCalled;
    }

    public function isFooterCalled(): bool
    {
        return $this->footerCalled;
    }

    public function isUsed(): bool
    {
        return $this->isHeaderCalled() || $this->isFooterCalled();
    }

    private function prepareInteractor(): void
    {
        if ($this->instrument) {
            $this->interactor->disableAutoRUM();
        }

        foreach ($this->newRelic->getCustomMetrics() as $name => $value) {
            $this->interactor->addCustomMetric((string) $name, (float) $value);
        }

        foreach ($this->newRelic->getCustomParameters() as $name => $value) {
            $this->interactor->addCustomParameter((string) $name, $value);
        }
    }
}
