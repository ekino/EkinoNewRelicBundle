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
     * @param GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if (null === $request = $this->validateRequest($event)) {
            return;
        }

        if ($this->newRelic->getName()) {
            if ($this->symfonyCache) {
                $this->interactor->startTransaction($this->newRelic->getName());
            }

            $this->interactor->setApplicationName($this->newRelic->getName(), $this->newRelic->getLicenseKey(), $this->newRelic->getXmit());
        }
    }

    /**
     * Set the name of the transaction
     *
     * @param GetResponseEvent $event
     */
    public function setTransactionName(GetResponseEvent $event)
    {
        if (null === $request = $this->validateRequest($event)) {
            return;
        }

        $transactionName = $this->transactionNamingStrategy->getTransactionName($request);

        $this->interactor->setTransactionName($transactionName);
    }

    /**
     * Make sure that it is not a subrequest and that we ignore paths that should be ignored
     *
     * @param GetResponseEvent $event
     *
     * @return \Symfony\Component\HttpFoundation\Request|null Return a Request or null iff it is not a master request
     */
    protected function validateRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if (in_array($request->get('_route'), $this->ignoredRoutes)) {
            $this->interactor->ignoreTransaction();
        }

        if (in_array($request->getPathInfo(), $this->ignoredPaths)) {
            $this->interactor->ignoreTransaction();
        }

        return $request;
    }
}
