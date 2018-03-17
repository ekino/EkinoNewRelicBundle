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

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

class NewRelicInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName(string $name, string $key = null, bool $xmit = false): bool
    {
        return newrelic_set_appname($name, $key, $xmit);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName(string $name): bool
    {
        return newrelic_name_transaction($name);
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction(): void
    {
        newrelic_ignore_transaction();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomEvent(string $name, array $attributes): void
    {
        newrelic_record_custom_event((string) $name, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric(string $name, float $value): bool
    {
        return newrelic_custom_metric($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter(string $name, $value): bool
    {
        return newrelic_add_custom_parameter((string) $name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_header($includeTags);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_footer($includeTags);
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM(): bool
    {
        return newrelic_disable_autorum();
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError(int $errno, string $errstr, string $errfile = null, int $errline = null, string $errcontext = null): void
    {
        newrelic_notice_error($errno, $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * {@inheritdoc}
     */
    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        newrelic_notice_error($message ?: $e->getMessage(), $e);
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob(): void
    {
        newrelic_background_job(true);
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob(): void
    {
        newrelic_background_job(false);
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction(bool $ignore = false): bool
    {
        return newrelic_end_transaction($ignore);
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction(string $name = null, string $license = null): bool
    {
        if (null === $name) {
            $name = ini_get('newrelic.appname');
        }

        return newrelic_start_transaction($name, $license);
    }

    /**
     * {@inheritdoc}
     */
    public function excludeFromApdex(): void
    {
        newrelic_ignore_apdex();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomTracer(string $name): bool
    {
        return newrelic_add_custom_tracer($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureParams(bool $enabled): void
    {
        newrelic_capture_params($enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function stopTransactionTiming(): void
    {
        newrelic_end_of_transaction();
    }

    /**
     * {@inheritdoc}
     */
    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        return newrelic_record_datastore_segment($func, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool
    {
        return newrelic_set_user_attributes($userValue, $accountValue, $productValue);
    }
}
