<?php

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    protected $interactor;
    protected $logger;

    public function __construct(NewRelicInteractorInterface $interactor, LoggerInterface $logger = null)
    {
        $this->interactor = $interactor;
        $this->logger = $logger;
    }

    protected function log($message, array $context = array())
    {
        if ($this->logger)
        {
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
        $this->interactor->getBrowserTimingHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        $this->log('Getting New Relic RUM timing footer');
        $this->interactor->getBrowserTimingFooter();
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
    public function noticeException(\Exception $e)
    {
        $this->log('Sending exception to New Relic');
        $this->interactor->noticeException($e);
    }
}