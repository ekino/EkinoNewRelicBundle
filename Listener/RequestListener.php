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

namespace Ekino\Bundle\NewRelicBundle\Listener;

use Ekino\Bundle\NewRelicBundle\NewRelic\Config;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListener
{
    private $ignoredRoutes;
    private $ignoredPaths;
    private $config;
    private $interactor;
    private $transactionNamingStrategy;
    private $symfonyCache;

    public function __construct(
        Config $config,
        NewRelicInteractorInterface $interactor,
        array $ignoreRoutes,
        array $ignoredPaths,
        TransactionNamingStrategyInterface $transactionNamingStrategy,
        bool $symfonyCache = false
    ) {
        $this->config = $config;
        $this->interactor = $interactor;
        $this->ignoredRoutes = $ignoreRoutes;
        $this->ignoredPaths = $ignoredPaths;
        $this->transactionNamingStrategy = $transactionNamingStrategy;
        $this->symfonyCache = $symfonyCache;
    }

    /**
     * Set the name of the application.
     *
     * @param GetResponseEvent $event
     */
    public function setApplicationName(GetResponseEvent $event)
    {
        if (!$this->validateEvent($event)) {
            return;
        }

        $appName = $this->config->getName();

        if ($appName) {
            if ($this->symfonyCache) {
                $this->interactor->startTransaction($appName);
            }

            // Set application name if different from ini configuration
            if ($appName !== ini_get('newrelic.appname')) {
                $this->interactor->setApplicationName($appName, $this->config->getLicenseKey(), $this->config->getXmit());
            }
        }
    }

    /**
     * Set the name of the transaction.
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
        if (in_array($request->get('_route'), $this->ignoredRoutes, true)) {
            $this->interactor->ignoreTransaction();
        }

        if (in_array($request->getPathInfo(), $this->ignoredPaths, true)) {
            $this->interactor->ignoreTransaction();
        }
    }

    /**
     * Make sure we should consider this event. Example: make sure it is a master request.
     *
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function validateEvent(GetResponseEvent $event)
    {
        return HttpKernelInterface::MASTER_REQUEST === $event->getRequestType();
    }
}
