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

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Logging interactor
 */
class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    /**
     * @var NewRelicInteractorInterface
     */
    protected $interactor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
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
     * Logs a given message
     *
     * @param string $message
     * @param array  $context
     */
    protected function log($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
        $this->log(sprintf('Setting New Relic Application name to %s', $name));
        $this->interactor->setApplicationName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        $this->log(sprintf('Setting New Relic Transaction name to %s', $name));
        $this->interactor->setTransactionName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic metric %s: %s', $name, $value));
        $this->interactor->addCustomMetric($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic parameter %s: %s', $name, $value));
        $this->interactor->addCustomParameter($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        $this->log('Getting New Relic RUM timing header');

        return $this->interactor->getBrowserTimingHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        $this->log('Getting New Relic RUM timing footer');

        return $this->interactor->getBrowserTimingFooter();
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        $this->log('Disabling New Relic Auto-RUM');
        $this->interactor->disableAutoRUM();
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        $this->log('Sending notice error to New Relic');
        $this->interactor->noticeError($msg);
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        $this->log('Sending exception to New Relic');
        $this->interactor->noticeException($e);
    }
}