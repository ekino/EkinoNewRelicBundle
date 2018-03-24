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

use Psr\Log\LoggerInterface;

class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    private $interactor;
    private $logger;

    public function __construct(NewRelicInteractorInterface $interactor, LoggerInterface $logger = null)
    {
        $this->interactor = $interactor;
        $this->logger = $logger;
    }

    /**
     * Logs a given message.
     *
     * @param string $message
     * @param array  $context
     */
    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    public function setApplicationName(string $name, string $key = null, bool $xmit = false): bool
    {
        $this->log(sprintf('Setting New Relic Application name to %s', $name));

        return $this->interactor->setApplicationName($name, $key, $xmit);
    }

    public function setTransactionName(string $name): bool
    {
        $this->log(sprintf('Setting New Relic Transaction name to %s', $name));

        return $this->interactor->setTransactionName($name);
    }

    public function ignoreTransaction(): void
    {
        $this->log('Ignoring transaction');
        $this->interactor->ignoreTransaction();
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->log(sprintf('Adding custom New Relic event %s', $name), $attributes);
        $this->interactor->addCustomEvent($name, $attributes);
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        $this->log(sprintf('Adding custom New Relic metric %s: %s', $name, $value));

        return $this->interactor->addCustomMetric($name, $value);
    }

    public function addCustomParameter(string $name, $value): bool
    {
        $this->log(sprintf('Adding custom New Relic parameter %s: %s', $name, $value));

        return $this->interactor->addCustomParameter($name, $value);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        $this->log('Getting New Relic RUM timing header');

        return $this->interactor->getBrowserTimingHeader($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        $this->log('Getting New Relic RUM timing footer');

        return $this->interactor->getBrowserTimingFooter($includeTags);
    }

    public function disableAutoRUM(): bool
    {
        $this->log('Disabling New Relic Auto-RUM');

        return $this->interactor->disableAutoRUM();
    }

    public function noticeError(int $errno, string $errstr, string $errfile = null, int $errline = null, string $errcontext = null): void
    {
        $this->log('Sending notice error to New Relic');
        $this->interactor->noticeError($errno, $errstr, $errfile, $errline, $errcontext);
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        $this->log('Sending exception to New Relic');
        $this->interactor->noticeThrowable($e, $message);
    }

    public function enableBackgroundJob(): void
    {
        $this->log('Enabling New Relic background job');
        $this->interactor->enableBackgroundJob();
    }

    public function disableBackgroundJob(): void
    {
        $this->log('Disabling New Relic background job');
        $this->interactor->enableBackgroundJob();
    }

    public function endTransaction(bool $ignore = false): bool
    {
        $this->log('Ending a New Relic transaction');

        return $this->interactor->endTransaction($ignore);
    }

    public function startTransaction(string $name = null, string $license = null): bool
    {
        $this->log(sprintf('Starting a new New Relic transaction for app "%s"', $name));

        return $this->interactor->startTransaction($name, $license);
    }

    public function excludeFromApdex(): void
    {
        $this->log('Excluding current transaction from New Relic Apdex score');
        $this->interactor->excludeFromApdex();
    }

    public function addCustomTracer(string $name): bool
    {
        $this->log('Adding custom New Relic tracer');

        return $this->interactor->addCustomTracer($name);
    }

    public function setCaptureParams(bool $enabled): void
    {
        $this->log(sprintf('Toggle New Relic capture params to "%s"', $enabled ? 'true' : 'false'));
        $this->interactor->addCustomTracer($enabled);
    }

    public function stopTransactionTiming(): void
    {
        $this->log('Stopping New Relic timing');
        $this->interactor->excludeFromApdex();
    }

    public function recordDatastoreSegment(callable $func, array $parameters): void
    {
        $this->log('Adding custom New Relic datastore segment');
        $this->interactor->recordDatastoreSegment($func, $parameters);
    }

    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool
    {
        $this->log('Setting New Relic user attributes', [
            'user_value' => $userValue,
            'account_value' => $accountValue,
            'product_value' => $productValue,
        ]);

        return $this->interactor->setUserAttributes($userValue, $accountValue, $productValue);
    }
}
