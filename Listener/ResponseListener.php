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

namespace Ekino\NewRelicBundle\Listener;

use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\NewRelicBundle\Twig\NewRelicExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{
    private Config $newRelic;
    private NewRelicInteractorInterface $interactor;
    private bool $instrument;
    private bool $symfonyCache;
    private ?NewRelicExtension $newRelicTwigExtension;

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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -255],
            ],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $isMainRequest = $event->isMainRequest();

        if (!$isMainRequest) {
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
                if (!$response instanceof StreamedResponse
                    && str_starts_with($response->headers->get('Content-Type', ''), 'text/html')
                ) {
                    $responseContent = $response->getContent();
                    $response->setContent(''); // free the memory

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isHeaderCalled()) {
                        $responseContent = preg_replace('|<head>|i', '$0'.$this->interactor->getBrowserTimingHeader(), $responseContent);
                    }

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isFooterCalled()) {
                        $responseContent = preg_replace('|</body>|i', $this->interactor->getBrowserTimingFooter().'$0', $responseContent);
                    }

                    $response->setContent($responseContent);
                }
            }
        }

        if ($this->symfonyCache) {
            $this->interactor->endTransaction();
        }
    }
}
