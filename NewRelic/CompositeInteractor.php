<?php
namespace Ekino\Bundle\NewRelicBundle\NewRelic;

class CompositeInteractor implements NewRelicInteractorInterface
{
    /**
     * @var NewRelicInteractorInterface[]
     */
    protected $interactors = array();

    /**
     * Appends new interactor
     *
     * @param NewRelicInteractorInterface $interactor
     */
    public function addInteractor (NewRelicInteractorInterface $interactor)
    {
        $this->interactors[] = $interactor;
    }

    /**
     * {@inheritDoc}
     */
    function setApplicationName ($name, $key = null, $xmit = false)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->setApplicationName($name, $key, $xmit);
        }
    }

    /**
     * {@inheritDoc}
     */
    function setTransactionName ($name)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->setTransactionName($name);
        }
    }

    /**
     * {@inheritDoc}
     */
    function addCustomMetric ($name, $value)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->addCustomMetric($name, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    function addCustomParameter ($name, $value)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->addCustomParameter($name, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    function getBrowserTimingHeader ()
    {
        $header = '';
        foreach ($this->interactors as $interactor) {
            $header .= $interactor->getBrowserTimingHeader();
        }
        return $header;
    }

    /**
     * {@inheritDoc}
     */
    function getBrowserTimingFooter ()
    {
        $footer = '';
        foreach ($this->interactors as $interactor) {
            $footer .= $interactor->getBrowserTimingFooter();
        }
        return $footer;
    }

    /**
     * {@inheritDoc}
     */
    function disableAutoRUM ()
    {
        foreach ($this->interactors as $interactor) {
            $interactor->disableAutoRUM();
        }
    }

    /**
     * {@inheritDoc}
     */
    function noticeError ($msg)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->noticeError($msg);
        }
    }

    /**
     * {@inheritDoc}
     */
    function noticeException (\Exception $e)
    {
        foreach ($this->interactors as $interactor) {
            $interactor->noticeException($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    function enableBackgroundJob ()
    {
        foreach ($this->interactors as $interactor) {
            $interactor->enableBackgroundJob();
        }
    }

    /**
     * {@inheritDoc}
     */
    function disableBackgroundJob ()
    {
        foreach ($this->interactors as $interactor) {
            $interactor->disableBackgroundJob();
        }
    }
}
