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

/**
 * This is the service that talks to NewRelic.
 */
interface NewRelicInteractorInterface
{
    /**
     * Sets the New Relic app name, which controls data rollup.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_set_appname}
     */
    public function setApplicationName(string $name, string $license = null, bool $xmit = false): bool;

    /**
     * Set custom name for current transaction.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_name_transaction}
     */
    public function setTransactionName(string $name): bool;

    /**
     * Do not instrument the current transaction.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_ignore_transaction}
     */
    public function ignoreTransaction(): void;

    /**
     * Record a custom event with the given name and attributes.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_custom_event}
     */
    public function addCustomEvent(string $name, array $attributes): void;

    /**
     * Add a custom metric (in milliseconds) to time a component of your app not captured by default.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newreliccustommetric-php-agent-api}
     */
    public function addCustomMetric(string $name, float $value): bool;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_add_custom_parameter}.
     *
     * @param string|int|float $value should be a scalar
     */
    public function addCustomParameter(string $name, $value): bool;

    /**
     * Returns a New Relic Browser snippet to inject in the head of your HTML output.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_header}
     */
    public function getBrowserTimingHeader(bool $includeTags = true): string;

    /**
     * Returns a New Relic Browser snippet to inject at the end of the HTML output.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_footer}
     */
    public function getBrowserTimingFooter(bool $includeTags = true): string;

    /**
     * Disable automatic injection of the New Relic Browser snippet on particular pages.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_disable_autorum}
     */
    public function disableAutoRUM(): bool;

    /**
     * Use these calls to collect errors that the PHP agent does not collect automatically and to set the callback for
     * your own error and exception handler.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_notice_error}
     */
    public function noticeThrowable(\Throwable $e, string $message = null): void;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_notice_error}.
     */
    public function noticeError(int $errno, string $errstr, string $errfile = null, int $errline = null, string $errcontext = null): void;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_background_job}.
     */
    public function enableBackgroundJob(): void;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_background_job}.
     */
    public function disableBackgroundJob(): void;

    /**
     * If you previously ended a transaction you many want to start a new one.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_start_transaction}
     */
    public function startTransaction(string $name = null, string $license = null): bool;

    /**
     * Stop instrumenting the current transaction immediately.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_end_transaction}
     */
    public function endTransaction(bool $ignore = false): bool;

    /**
     * Ignore the current transaction when calculating Apdex.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_ignore_apdex}
     */
    public function excludeFromApdex(): void;

    /**
     * Specify functions or methods for the agent to target for custom instrumentation. This is the API equivalent of
     * the newrelic.transaction_tracer.custom setting.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_add_custom_tracer}
     */
    public function addCustomTracer(string $name): bool;

    /**
     * Enable or disable the capture of URL parameters.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_capture_params}
     */
    public function setCaptureParams(bool $enabled): void;

    /**
     * Stop timing the current transaction, but continue instrumenting it.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_end_of_transaction}
     */
    public function stopTransactionTiming(): void;

    /**
     * Records a datastore segment.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_datastore_segment}
     *
     * @return bool|mixed The return value of $func is returned. If an error occurs, false is returned.
     */
    public function recordDatastoreSegment(callable $func, array $parameters);

    /**
     * Create user-related custom attributes. newrelic_add_custom_parameter is more flexible.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_set_user_attributes}
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool;
}
