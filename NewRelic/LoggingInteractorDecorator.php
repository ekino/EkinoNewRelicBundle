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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    private $interactor;
    private $logger;

    public function __construct(NewRelicInteractorInterface $interactor, LoggerInterface $logger = null)
    {
        $this->interactor = $interactor;
        $this->logger = $logger ?? new NullLogger();
    }

    public function setApplicationName(string $name, string $key = null, bool $xmit = false): bool
    {
        $this->logger->debug('Setting New Relic Application name to {name}', ['name' => $name]);

        return $this->interactor->setApplicationName($name, $key, $xmit);
    }

    public function setTransactionName(string $name): bool
    {
        $this->logger->debug('Setting New Relic Transaction name to {name}', ['name' => $name]);

        return $this->interactor->setTransactionName($name);
    }

    public function ignoreTransaction(): void
    {
        $this->logger->debug('Ignoring transaction');
        $this->interactor->ignoreTransaction();
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->logger->debug('Adding custom New Relic event {name}', ['name' => $name, 'attributes' => $attributes]);
        $this->interactor->addCustomEvent($name, $attributes);
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        $this->logger->debug('Adding custom New Relic metric {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addCustomMetric($name, $value);
    }

    public function addCustomParameter(string $name, $value): bool
    {
        $this->logger->debug('Adding custom New Relic parameters {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addCustomParameter($name, $value);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        $this->logger->debug('Getting New Relic RUM timing header');

        return $this->interactor->getBrowserTimingHeader($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        $this->logger->debug('Getting New Relic RUM timing footer');

        return $this->interactor->getBrowserTimingFooter($includeTags);
    }

    public function disableAutoRUM(): bool
    {
        $this->logger->debug('Disabling New Relic Auto-RUM');

        return $this->interactor->disableAutoRUM();
    }

    public function noticeError(int $errno, string $errstr, string $errfile = null, int $errline = null, string $errcontext = null): void
    {
        $this->logger->debug('Sending notice error to New Relic');
        $this->interactor->noticeError($errno, $errstr, $errfile, $errline, $errcontext);
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        $this->logger->debug('Sending exception to New Relic');
        $this->interactor->noticeThrowable($e, $message);
    }

    public function enableBackgroundJob(): void
    {
        $this->logger->debug('Enabling New Relic background job');
        $this->interactor->enableBackgroundJob();
    }

    public function disableBackgroundJob(): void
    {
        $this->logger->debug('Disabling New Relic background job');
        $this->interactor->disableBackgroundJob();
    }

    public function endTransaction(bool $ignore = false): bool
    {
        $this->logger->debug('Ending a New Relic transaction');

        return $this->interactor->endTransaction($ignore);
    }

    public function startTransaction(string $name = null, string $license = null): bool
    {
        $this->logger->debug('Starting a new New Relic transaction for app {name}', ['name' => $name]);

        return $this->interactor->startTransaction($name, $license);
    }

    public function excludeFromApdex(): void
    {
        $this->logger->debug('Excluding current transaction from New Relic Apdex score');
        $this->interactor->excludeFromApdex();
    }

    public function addCustomTracer(string $name): bool
    {
        $this->logger->debug('Adding custom New Relic tracer');

        return $this->interactor->addCustomTracer($name);
    }

    public function setCaptureParams(bool $enabled): void
    {
        $this->logger->debug('Toggle New Relic capture params to {enabled}', ['enabled' => $enabled]);
        $this->interactor->setCaptureParams($enabled);
    }

    public function stopTransactionTiming(): void
    {
        $this->logger->debug('Stopping New Relic timing');
        $this->interactor->stopTransactionTiming();
    }

    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        $this->logger->debug('Adding custom New Relic datastore segment');

        return $this->interactor->recordDatastoreSegment($func, $parameters);
    }

    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool
    {
        $this->logger->debug('Setting New Relic user attributes', [
            'user_value' => $userValue,
            'account_value' => $accountValue,
            'product_value' => $productValue,
        ]);

        return $this->interactor->setUserAttributes($userValue, $accountValue, $productValue);
    }
}
