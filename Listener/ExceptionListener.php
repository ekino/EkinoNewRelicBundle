<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


class ExceptionListener
{
    protected $interactor;

    /**
     * @param NewRelic $newRelic
     */
    public function __construct(NewRelicInteractorInterface $interactor)
    {
        $this->interactor = $interactor;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceOf HttpExceptionInterface)
        {
            $this->interactor->noticeException($exception);
        }
    }
}
