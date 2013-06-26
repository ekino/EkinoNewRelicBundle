<?php

namespace Ekino\Bundle\NewRelicBundle\Listener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

class CommandListener
{
    /**
     * @var NewRelicInteractorInterface
     */
    protected $interactor;

    /**
     * @param NewRelicInteractorInterface $interactor
     */
    public function __construct(NewRelicInteractorInterface $interactor)
    {
        $this->interactor = $interactor;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $this->interactor->enableBackgroundJob();
    }
}