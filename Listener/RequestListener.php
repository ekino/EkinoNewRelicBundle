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
    protected $ignoreRoutes;

    protected $ignoreUrls;

    protected $newRelic;

    protected $interactor;

    /**
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param array                       $ignoreRoutes
     * @param array                       $ignoreUrls
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, array $ignoreRoutes, array $ignoreUrls)
    {
        $this->interactor   = $interactor;
        $this->newRelic     = $newRelic;
        $this->ignoreRoutes = $ignoreRoutes;
        $this->ignoreUrls   = $ignoreUrls;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $route = $event->getRequest()->get('_route');

        $this->interactor->setApplicationName($this->newRelic->getName());
        $this->interactor->setTransactionName($route ?: 'symfony unknown route');
    }
}