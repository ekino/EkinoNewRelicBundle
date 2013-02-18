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

/**
 * Newrelic response listener
 */
class ResponseListener
{
    /**
     * @var NewRelic
     */
    protected $newRelic;

    /**
     * @var NewRelicInteractorInterface
     */
    protected $interactor;

    /**
     * @var boolean
     */
    protected $instrument;

    /**
     * Constructor
     *
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param boolean                     $instrument
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, $instrument = false)
    {
        $this->newRelic   = $newRelic;
        $this->interactor = $interactor;
        $this->instrument = $instrument;
    }

    /**
     * On core response
     *
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

        if ($this->instrument) {
            $this->interactor->disableAutoRUM();

            // Some requests might not want to get instrumented
            if ($event->getRequest()->attributes->get('_instrument', true)) {
                $response = $event->getResponse();

                // We can only instrument HTML responses
                if (substr($response->headers->get('Content-Type'), 0, 9) == 'text/html') {
                    $responseContent = $response->getContent();

                    $responseContent = preg_replace('/<\s*head\s*>/', '$0'.$this->interactor->getBrowserTimingHeader(), $responseContent);
                    $responseContent = preg_replace('/<\s*\/\s*body\s*>/', $this->interactor->getBrowserTimingFooter().'$0', $responseContent);

                    if ($responseContent) {
                        $response->setContent($responseContent);
                    }
                }
            }
        }
    }
}