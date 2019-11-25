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
use Ekino\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    private $ignoredRoutes;
    private $ignoredPaths;
    private $config;
    private $interactor;
    private $transactionNamingStrategy;
    private $symfonyCache;

    public function __construct(
        Config $config,
        NewRelicInteractorInterface $interactor,
        array $ignoreRoutes,
        array $ignoredPaths,
        TransactionNamingStrategyInterface $transactionNamingStrategy,
        bool $symfonyCache = false
    ) {
        $this->config = $config;
        $this->interactor = $interactor;
        $this->ignoredRoutes = $ignoreRoutes;
        $this->ignoredPaths = $ignoredPaths;
        $this->transactionNamingStrategy = $transactionNamingStrategy;
        $this->symfonyCache = $symfonyCache;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                 ['setApplicationName', 255],
                 ['setIgnoreTransaction', 31],
                 ['setTransactionName', -10],
            ],
        ];
    }

    /**
     * @param GetResponseEvent|ResponseEvent $event
     */
    public function setApplicationName($event): void
    {
        if (!$event instanceof GetResponseEvent && !$event instanceof ResponseEvent) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of type %s, %s given', \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class, \is_object($event) ? \get_class($event) : \gettype($event)));
        }

        if (!$this->isEventValid($event)) {
            return;
        }

        $appName = $this->config->getName();

        if (!$appName) {
            return;
        }

        if ($this->symfonyCache) {
            $this->interactor->startTransaction($appName);
        }

        // Set application name if different from ini configuration
        if ($appName !== \ini_get('newrelic.appname')) {
            $this->interactor->setApplicationName($appName, $this->config->getLicenseKey(), $this->config->getXmit());
        }
    }

    /**
     * @param GetResponseEvent|ResponseEvent $event
     */
    public function setTransactionName($event): void
    {
        if (!$event instanceof GetResponseEvent && !$event instanceof ResponseEvent) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of type %s, %s given', \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class, \is_object($event) ? \get_class($event) : \gettype($event)));
        }

        if (!$this->isEventValid($event)) {
            return;
        }

        $transactionName = $this->transactionNamingStrategy->getTransactionName($event->getRequest());

        $this->interactor->setTransactionName($transactionName);
    }

    /**
     * @param GetResponseEvent|ResponseEvent $event
     */
    public function setIgnoreTransaction($event): void
    {
        if (!$event instanceof GetResponseEvent && !$event instanceof ResponseEvent) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of type %s, %s given', \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class, \is_object($event) ? \get_class($event) : \gettype($event)));
        }

        if (!$this->isEventValid($event)) {
            return;
        }

        $request = $event->getRequest();
        if (\in_array($request->get('_route'), $this->ignoredRoutes, true)) {
            $this->interactor->ignoreTransaction();
        }

        if (\in_array($request->getPathInfo(), $this->ignoredPaths, true)) {
            $this->interactor->ignoreTransaction();
        }
    }

    /**
     * Make sure we should consider this event. Example: make sure it is a master request.
     *
     * @param GetResponseEvent|ResponseEvent $event
     */
    private function isEventValid($event): bool
    {
        if (!$event instanceof GetResponseEvent && !$event instanceof ResponseEvent) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of type %s, %s given', \class_exists(ResponseEvent::class) ? ResponseEvent::class : GetResponseEvent::class, \is_object($event) ? \get_class($event) : \gettype($event)));
        }

        return HttpKernelInterface::MASTER_REQUEST === $event->getRequestType();
    }
}
