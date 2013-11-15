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
    protected $newRelic;

    protected $interactor;

    protected $transactionNamingStrategy;

    /**
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param array                       $ignoreRoutes
     * @param array                       $ignoreUrls
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, TransactionNamingStrategyInterface $transactionNamingStrategy)
    {
        $this->interactor   = $interactor;
        $this->newRelic     = $newRelic;
        $this->transactionNamingStrategy = $transactionNamingStrategy;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $transactionName = $this->transactionNamingStrategy->getTransactionName($event->getRequest());

        if ($this->newRelic->getName()) {
            $this->interactor->setApplicationName($this->newRelic->getName(), $this->newRelic->getLicenseKey(), $this->newRelic->getXmit());
        }
        $this->interactor->setTransactionName($transactionName);
    }
}
