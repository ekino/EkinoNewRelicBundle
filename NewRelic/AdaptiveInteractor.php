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

namespace Ekino\NewRelicBundle\NewRelic;

/**
 * This interactor does never assume that the NewRelic extension is installed. It will check
 * for the existence of each method EVERY time. This is a good interactor to use when you want
 * to enable and disable the NewRelic extension without rebuilding your container.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AdaptiveInteractor implements NewRelicInteractorInterface
{
    private $interactor;

    public function __construct(NewRelicInteractorInterface $real, NewRelicInteractorInterface $fake)
    {
        $this->interactor = \extension_loaded('newrelic') ? $real : $fake;
    }

    public function setApplicationName(string $name, string $license = null, bool $xmit = false): bool
    {
        return $this->interactor->setApplicationName($name, $license, $xmit);
    }

    public function setTransactionName(string $name): bool
    {
        return $this->interactor->setTransactionName($name);
    }

    public function ignoreTransaction(): void
    {
        $this->interactor->ignoreTransaction();
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->interactor->addCustomEvent($name, $attributes);
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        return $this->interactor->addCustomMetric($name, $value);
    }

    public function addCustomParameter(string $name, $value): bool
    {
        return $this->interactor->addCustomParameter($name, $value);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return $this->interactor->getBrowserTimingHeader($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return $this->interactor->getBrowserTimingFooter($includeTags);
    }

    public function disableAutoRUM(): bool
    {
        return $this->interactor->disableAutoRUM();
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        $this->interactor->noticeThrowable($e, $message);
    }

    public function noticeError(
        int $errno,
        string $errstr,
        string $errfile = null,
        int $errline = null,
        string $errcontext = null
    ): void {
        $this->interactor->noticeError($errno, $errstr, $errfile, $errline, $errcontext);
    }

    public function enableBackgroundJob(): void
    {
        $this->interactor->enableBackgroundJob();
    }

    public function disableBackgroundJob(): void
    {
        $this->interactor->disableBackgroundJob();
    }

    public function startTransaction(string $name = null, string $license = null): bool
    {
        return $this->interactor->startTransaction($name, $license);
    }

    public function endTransaction(bool $ignore = false): bool
    {
        return $this->interactor->endTransaction($ignore);
    }

    public function excludeFromApdex(): void
    {
        $this->interactor->excludeFromApdex();
    }

    public function addCustomTracer(string $name): bool
    {
        return $this->interactor->addCustomTracer($name);
    }

    public function setCaptureParams(bool $enabled): void
    {
        $this->interactor->setCaptureParams($enabled);
    }

    public function stopTransactionTiming(): void
    {
        $this->interactor->stopTransactionTiming();
    }

    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        return $this->interactor->recordDatastoreSegment($func, $parameters);
    }

    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool
    {
        return $this->interactor->setUserAttributes($userValue, $accountValue, $productValue);
    }
}
