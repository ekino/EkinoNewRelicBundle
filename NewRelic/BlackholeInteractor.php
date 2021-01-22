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
 * This interactor throw away any call.
 *
 * It can be used to avoid conditional log calls.
 */
class BlackholeInteractor implements NewRelicInteractorInterface
{
    public function setApplicationName(string $name, string $license = null, bool $xmit = false): bool
    {
        return true;
    }

    public function setTransactionName(string $name): bool
    {
        return true;
    }

    public function ignoreTransaction(): void
    {
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        return true;
    }

    public function addCustomParameter(string $name, $value): bool
    {
        return true;
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return '';
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return '';
    }

    public function disableAutoRUM(): ?bool
    {
        return true;
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

    public function startTransaction(string $name = null, string $license = null): bool
    {
        return true;
    }

    public function endTransaction(bool $ignore = false): bool
    {
        return true;
    }

    public function excludeFromApdex(): void
    {
    }

    public function addCustomTracer(string $name): bool
    {
        return true;
    }

    public function setCaptureParams(bool $enabled): void
    {
    }

    public function stopTransactionTiming(): void
    {
    }

    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        return $func();
    }

    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool
    {
        return true;
    }
}
