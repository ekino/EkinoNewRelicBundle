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

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Listen to exceptions dispatched by Symfony to log them to NewRelic.
 */
class ExceptionListener
{
    private $interactor;

    public function __construct(NewRelicInteractorInterface $interactor)
    {
        $this->interactor = $interactor;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof HttpExceptionInterface) {
            $this->interactor->noticeThrowable($exception);
        }
    }
}
