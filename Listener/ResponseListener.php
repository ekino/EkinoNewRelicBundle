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
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;


class ResponseListener
{
    protected $newRelic;

    protected $interactor;

    /**
     * @param NewRelic $newRelic
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor)
    {
        $this->newRelic   = $newRelic;
        $this->interactor = $interactor;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        foreach ($this->newRelic->getCustomMetrics() as $name => $value) {
            $this->interactor->addCustomMetric($name, $value);
        }

        foreach ($this->newRelic->getCustomParameters() as $name => $value) {
            $this->interactor->addCustomParameter($name, $value);
        }
    }
}