<?php

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