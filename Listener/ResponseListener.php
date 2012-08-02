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

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    protected $ignoreRoutes;

    protected $ignoreUrls;

    /**
     * @param NewRelic $newRelic
     */
    public function __construct(NewRelic $newRelic)
    {
        $this->newRelic = $newRelic;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        foreach ($this->newRelic->getCustomMetrics() as $name => $value) {
            newrelic_custom_metric($name, (double) $value);
        }

        foreach ($this->newRelic->getCustomParameters() as $name => $value) {
            newrelic_add_custom_parameter($name, $value);
        }
    }
}