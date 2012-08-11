<?php

/*
 * This file is part of the Sonata project.
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

class RequestListener
{
    const TRANSACTION_NAMING_ROUTE = 0;
    const TRANSACTION_NAMING_CONTROLLER = 1;

    protected $ignoreRoutes;

    protected $ignoreUrls;

    protected $newRelic;

    protected $interactor;

    protected $transactionNaming;

    /**
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param array                       $ignoreRoutes
     * @param array                       $ignoreUrls
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, array $ignoreRoutes, array $ignoreUrls, $transactionNaming = self::TRANSACTION_NAMING_ROUTE)
    {
        $this->interactor   = $interactor;
        $this->newRelic     = $newRelic;
        $this->ignoreRoutes = $ignoreRoutes;
        $this->ignoreUrls   = $ignoreUrls;
        $this->transactionNaming = $transactionNaming;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if ($this->transactionNaming == self::TRANSACTION_NAMING_ROUTE)
        {
            $route = $event->getRequest()->get('_route');
            $this->interactor->setTransactionName($route ?: 'Unknown Symfony route');
        }
        else if ($this->transactionNaming == self::TRANSACTION_NAMING_CONTROLLER)
        {
            $controller = $event->getRequest()->get('_controller');
            $this->interactor->setTransactionName($controller ?: 'Unknown Symfony controller');
        }

        $this->interactor->setApplicationName($this->newRelic->getName());
    }
}