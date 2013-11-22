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
 * Logging interactor
 */
class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface             $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
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
    public function setApplicationName($name, $key = null, $xmit = false)
    {
        $this->log(sprintf('Setting New Relic Application name to %s', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        $this->log(sprintf('Setting New Relic Transaction name to %s', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic metric %s: %s', $name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic parameter %s: %s', $name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        $this->log('Getting New Relic RUM timing header');
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        $this->log('Getting New Relic RUM timing footer');
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        $this->log('Disabling New Relic Auto-RUM');
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        $this->log('Sending notice error to New Relic');
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        $this->log('Sending exception to New Relic');
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob()
    {
        $this->log('Enabling New Relic background job');
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob()
    {
        $this->log('Disabling New Relic background job');
    }
}
