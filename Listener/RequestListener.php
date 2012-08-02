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

class RequestListener
{
    protected $ignoreRoutes;

    protected $ignoreUrls;

    protected $newRelic;

    /**
     * @param NewRelic $newRelic
     * @param array    $ignoreRoutes
     * @param array    $ignoreUrls
     */
    public function __construct(NewRelic $newRelic, array $ignoreRoutes, array $ignoreUrls)
    {
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

        newrelic_name_transaction($route ?: 'symfony unknow route');
        newrelic_set_appname($this->newRelic->getName());
    }
}