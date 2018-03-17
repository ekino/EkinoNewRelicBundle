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

/**
 * This interactor does never assume that the NewRelic extension is installed. It will check
 * for the existence of each method EVERY time. This is a good interactor to use when you want
 * to enable and disable the NewRelic extension without rebuilding your container.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AutoInteractor implements NewRelicInteractorInterface
{
    /**
     * @var NewRelicInteractorInterface
     */
    private $interactor;

    /**
     * @param NewRelicInteractorInterface $interactor
     */
    public function __construct(NewRelicInteractorInterface $real, NewRelicInteractorInterface $fake)
    {
        $this->interactor = extension_loaded('newrelic') ? $real : $fake;
    }


    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name, $key = null, $xmit = false)
    {
        $this->interactor->setApplicationName($name, $key, $xmit);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        $this->interactor->setTransactionName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction ()
    {
        $this->interactor->ignoreTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        $this->interactor->addCustomMetric($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        $this->interactor->addCustomParameter($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return $this->interactor->getBrowserTimingHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return $this->interactor->getBrowserTimingFooter();
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        $this->interactor->disableAutoRUM();
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        $this->interactor->noticeError($msg);
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        $this->interactor->noticeError($e);
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob()
    {
        $this->interactor->enableBackgroundJob();
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob()
    {
        $this->interactor->disableBackgroundJob();
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction()
    {
        $this->interactor->endTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction($name)
    {
        $this->interactor->startTransaction($name);
    }
}
