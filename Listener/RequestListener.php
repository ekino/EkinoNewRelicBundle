<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;

class RequestListener
{
    protected $ignoredRoutes;

    protected $ignoredPaths;

    protected $newRelic;

    protected $interactor;

    protected $transactionNamingStrategy;

    /**
     * @var boolean
     */
    protected $symfonyCache;

    /**
     * @param NewRelic                           $newRelic
     * @param NewRelicInteractorInterface        $interactor
     * @param array                              $ignoreRoutes
     * @param array                              $ignoredPaths
     * @param TransactionNamingStrategyInterface $transactionNamingStrategy
     * @param boolean                            $symfonyCache
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, array $ignoreRoutes, array $ignoredPaths, TransactionNamingStrategyInterface $transactionNamingStrategy, $symfonyCache = false)
    {
        $this->interactor    = $interactor;
        $this->newRelic      = $newRelic;
        $this->ignoredRoutes = $ignoreRoutes;
        $this->ignoredPaths  = $ignoredPaths;
        $this->transactionNamingStrategy = $transactionNamingStrategy;
        $this->symfonyCache      = $symfonyCache;
    }

    /**
     * Set the name of the application
     *
     * @param GetResponseEvent $event
     */
    public function setApplicationName(GetResponseEvent $event)
    {
        if (!$this->validateEvent($event)) {
            return;
        }

        $appName = $this->newRelic->getName();

        if ($appName) {
            if ($this->symfonyCache) {
                $this->interactor->startTransaction($appName);
            }

            // Set application name if different from ini configuration
            if ($appName !== ini_get('newrelic.appname')) {
                $this->interactor->setApplicationName($appName, $this->newRelic->getLicenseKey(), $this->newRelic->getXmit());
            }
        }
    }

    /**
     * Set the name of the transaction
     *
     * @param GetResponseEvent $event
     */
    public function setTransactionName(GetResponseEvent $event)
    {
        if (!$this->validateEvent($event)) {
            return;
        }

        $transactionName = $this->transactionNamingStrategy->getTransactionName($event->getRequest());

        $this->interactor->setTransactionName($transactionName);
    }

    /**
     * @param GetResponseEvent $event
     */
    public function setIgnoreTransaction(GetResponseEvent $event)
    {
        if (!$this->validateEvent($event)) {
            return;
        }

        $request = $event->getRequest();
        if (in_array($request->get('_route'), $this->ignoredRoutes)) {
            $this->interactor->ignoreTransaction();
        }

        if (in_array($request->getPathInfo(), $this->ignoredPaths)) {
            $this->interactor->ignoreTransaction();
        }
    }

    /**
     * Make sure we should consider this event. Example: make sure it is a master request
     *
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    protected function validateEvent(GetResponseEvent $event)
    {
        return $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST;
    }
}
