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

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listen to exceptions dispatched by Symfony to log them to NewRelic.
 */
class ExceptionListener implements EventSubscriberInterface
{
    private NewRelicInteractorInterface $interactor;

    public function __construct(NewRelicInteractorInterface $interactor)
    {
        $this->interactor = $interactor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof HttpExceptionInterface) {
            $this->interactor->noticeThrowable($exception);
        }
    }
}
