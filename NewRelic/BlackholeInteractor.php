<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

/**
 * Do not log anything.
 */
class BlackholeInteractor implements NewRelicInteractorInterface
{
    public function setApplicationName(string $name, string $license = null, bool $xmit = false): void
    {
    }

    public function setTransactionName(string $name): void
    {
    }

    public function ignoreTransaction(): void
    {
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
    }

    public function addCustomMetric(string $name, float $value): void
    {
    }

    public function addCustomParameter(string $name, $value): void
    {
    }

    public function getBrowserTimingHeader(bool $includeTags): string
    {
        return '';
    }

    public function getBrowserTimingFooter(bool $includeTags): string
    {
        return '';
    }

    public function disableAutoRUM(): void
    {
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
    }

    public function noticeError(
        int $errno,
        string $errstr,
        string $errfile = null,
        int $errline = null,
        string $errcontext = null
    ): void {
    }

    public function enableBackgroundJob(): void
    {
    }

    public function disableBackgroundJob(): void
    {
    }

    public function startTransaction(string $name, string $license = null): void
    {
    }

    public function endTransaction(): void
    {
    }

    public function excludeFromApdex(): void
    {
    }

    public function addCustomTracer(string $name): void
    {
    }

    public function setCaptureParams(bool $enabled): void
    {
    }

    public function stopTransactionTiming(): void
    {
    }

    public function recordDatastoreSegment(callable $func, array $parameters): void
    {
    }

    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): void
    {
    }
}
