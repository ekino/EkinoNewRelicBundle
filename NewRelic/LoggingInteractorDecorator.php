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

use Psr\Log\LoggerInterface;

/**
 * Logging interactor.
 */
class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    /**
     * @var NewRelicInteractorInterface
     */
    private $interactor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param NewRelicInteractorInterface $interactor
     * @param LoggerInterface             $logger
     */
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
    protected function log($message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setApplicationName(string $name, string $key = null, bool $xmit = false): void
    {
        $this->log(sprintf('Setting New Relic Application name to %s', $name));
        $this->interactor->setApplicationName($name, $key, $xmit);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName(string $name): void
    {
        $this->log(sprintf('Setting New Relic Transaction name to %s', $name));
        $this->interactor->setTransactionName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction(): void
    {
        $this->log('Ignoring transaction');
        $this->interactor->ignoreTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->log(sprintf('Adding custom New Relic event %s', $name), $attributes);
        $this->interactor->addCustomEvent($name, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric(string $name, float $value): void
    {
        $this->log(sprintf('Adding custom New Relic metric %s: %s', $name, $value));
        $this->interactor->addCustomMetric($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter(string $name, $value): void
    {
        $this->log(sprintf('Adding custom New Relic parameter %s: %s', $name, $value));
        $this->interactor->addCustomParameter($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader(bool $includeTags): string
    {
        $this->log('Getting New Relic RUM timing header');

        return $this->interactor->getBrowserTimingHeader($includeTags);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter(bool $includeTags): string
    {
        $this->log('Getting New Relic RUM timing footer');

        return $this->interactor->getBrowserTimingFooter($includeTags);
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM(): void
    {
        $this->log('Disabling New Relic Auto-RUM');
        $this->interactor->disableAutoRUM();
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError(int $errno, string $errstr, string $errfile = null, int $errline = null, string $errcontext = null): void
    {
        $this->log('Sending notice error to New Relic');
        $this->interactor->noticeError($errno, $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * {@inheritdoc}
     */
    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        $this->log('Sending exception to New Relic');
        $this->interactor->noticeThrowable($e, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob(): void
    {
        $this->log('Enabling New Relic background job');
        $this->interactor->enableBackgroundJob();
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob(): void
    {
        $this->log('Disabling New Relic background job');
        $this->interactor->enableBackgroundJob();
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction(): void
    {
        $this->log('Ending a New Relic transaction');
        $this->interactor->endTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction(string $name, string $license = null): void
    {
        $this->log(sprintf('Starting a new New Relic transaction for app "%s"', $name));
        $this->interactor->startTransaction($name, $license);
    }

    /**
     * {@inheritdoc}
     */
    public function excludeFromApdex(): void
    {
        $this->log('Excluding current transaction from New Relic Apdex score');
        $this->interactor->excludeFromApdex();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomTracer(string $name): void
    {
        $this->log('Adding custom New Relic tracer');
        $this->interactor->addCustomTracer($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureParams(bool $enabled): void
    {
        $this->log(sprintf('Toggle New Relic capture params to "%s"', $enabled ? 'true' : 'false'));
        $this->interactor->addCustomTracer($enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function stopTransactionTiming(): void
    {
        $this->log('Stopping New Relic timing');
        $this->interactor->excludeFromApdex();
    }

    /**
     * {@inheritdoc}
     */
    public function recordDatastoreSegment(callable $func, array $parameters): void
    {
        $this->log('Adding custom New Relic datastore segment');
        $this->interactor->recordDatastoreSegment($func, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue): void
    {
        $this->log('Setting New Relic user attributes', [
            'user_value' => $userValue,
            'account_value' => $accountValue,
            'product_value' => $productValue,
        ]);
        $this->interactor->setUserAttributes($userValue, $accountValue, $productValue);
    }
}
