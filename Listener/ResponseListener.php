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
use Ekino\Bundle\NewRelicBundle\Twig\NewRelicExtension;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Newrelic response listener.
 */
class ResponseListener
{
    private $newRelic;
    private $interactor;
    private $instrument;
    private $symfonyCache;
    private $newRelicTwigExtension;

    public function __construct(
        Config $newRelic,
        NewRelicInteractorInterface $interactor,
        bool $instrument = false,
        bool $symfonyCache = false,
        NewRelicExtension $newRelicTwigExtension = null
    ) {
        $this->newRelic = $newRelic;
        $this->interactor = $interactor;
        $this->instrument = $instrument;
        $this->symfonyCache = $symfonyCache;
        $this->newRelicTwigExtension = $newRelicTwigExtension;
    }

    /**
     * On core response.
     *
     * @param FilterResponseEvent $event
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isUsed()) {
            foreach ($this->newRelic->getCustomMetrics() as $name => $value) {
                $this->interactor->addCustomMetric((string) $name, (float) $value);
            }

            foreach ($this->newRelic->getCustomParameters() as $name => $value) {
                $this->interactor->addCustomParameter((string) $name, $value);
            }
        }

        foreach ($this->newRelic->getCustomEvents() as $name => $events) {
            foreach ($events as $attributes) {
                $this->interactor->addCustomEvent((string) $name, $attributes);
            }
        }

        if ($this->instrument) {
            if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isUsed()) {
                $this->interactor->disableAutoRUM();
            }

            // Some requests might not want to get instrumented
            if ($event->getRequest()->attributes->get('_instrument', true)) {
                $response = $event->getResponse();

                // We can only instrument HTML responses
                if ('text/html' === substr($response->headers->get('Content-Type'), 0, 9)) {
                    $responseContent = $response->getContent();

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isHeaderCalled()) {
                        $responseContent = preg_replace('/<\s*head\s*>/', '$0'.$this->interactor->getBrowserTimingHeader(), $responseContent);
                    }

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isFooterCalled()) {
                        $responseContent = preg_replace('/<\s*\/\s*body\s*>/', $this->interactor->getBrowserTimingFooter().'$0', $responseContent);
                    }

                    if ($responseContent) {
                        $response->setContent($responseContent);
                    }
                }
            }
        }

        if ($this->symfonyCache) {
            $this->interactor->endTransaction();
        }
    }
}
