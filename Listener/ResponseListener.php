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

    protected $instrument;

    /**
     * @param NewRelic $newRelic
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, $instrument = false)
    {
        $this->newRelic   = $newRelic;
        $this->interactor = $interactor;
        $this->instrument = $instrument;
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

        if ($this->instrument)
        {
            $this->interactor->disableAutoRUM();
            // Some requests might not want to get instrumented
            if ($event->getRequest()->attributes->get('_instrument', true))
            {
              $response = $event->getResponse();

              // We can only instrument HTML responses
              if ($response->headers->get('Content-Type') == 'text/html')
              {
                $response_content = $response->getContent();

                $response_content = preg_replace('/<\s*head\s*>/', '$0'.$this->interactor->getBrowserTimingHeader(), $response_content);
                $response_content = preg_replace('/<\s*\/\s*body\s*>/', $this->interactor->getBrowserTimingFooter().'$0', $response_content);

                if ($response_content)
                {
                  $response->setContent($response_content);
                }
              }
            }
        }
    }
}