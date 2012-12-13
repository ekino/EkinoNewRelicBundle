<?php

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

class BlackholeInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
    }
}