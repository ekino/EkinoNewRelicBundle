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
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -255],
            ],
        ];
    }

    public function onKernelResponse(KernelResponseEvent $event): void
    {
        $isMainRequest = method_exists($event, 'isMainRequest')
            ? $event->isMainRequest()
            : $event->isMasterRequest()
        ;
        
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
                    && 'text/html' === \substr($response->headers->get('Content-Type', ''), 0, 9)
                ) {
                    $responseContent = $response->getContent();
                    $response->setContent(''); // free the memory

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isHeaderCalled()) {
                        $responseContent = \preg_replace('|<head>|i', '$0'.$this->interactor->getBrowserTimingHeader(), $responseContent);
                    }

                    if (null === $this->newRelicTwigExtension || false === $this->newRelicTwigExtension->isFooterCalled()) {
                        $responseContent = \preg_replace('|</body>|i', $this->interactor->getBrowserTimingFooter().'$0', $responseContent);
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

if (!\class_exists(KernelResponseEvent::class)) {
    if (\class_exists(ResponseEvent::class)) {
        \class_alias(ResponseEvent::class, KernelResponseEvent::class);
    } else {
        \class_alias(FilterResponseEvent::class, KernelResponseEvent::class);
    }
}
