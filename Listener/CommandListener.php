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

use Ekino\Bundle\NewRelicBundle\Exception\ThrowableException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandListener implements EventSubscriberInterface
{
    /**
     * @var NewRelicInteractorInterface
     */
    protected $interactor;

    protected $newRelic;

    protected $ignoredCommands;

    /**
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param array                       $ignoredCommands
     */
    public function __construct(NewRelic $newRelic, NewRelicInteractorInterface $interactor, array $ignoredCommands)
    {
        $this->interactor = $interactor;
        $this->newRelic = $newRelic;
        $this->ignoredCommands = $ignoredCommands;
    }

    public static function getSubscribedEvents()
    {
        $events = array(
            ConsoleEvents::COMMAND => array('onConsoleCommand', 0),
            ConsoleEvents::ERROR => array('onConsoleError', 0),
        );

        return $events;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $input = $event->getInput();

        if ($this->newRelic->getName()) {
            $this->interactor->setApplicationName($this->newRelic->getName(), $this->newRelic->getLicenseKey(), $this->newRelic->getXmit());
        }
        $this->interactor->setTransactionName($command->getName());

        // Due to newrelic's extension implementation, the method `ignoreTransaction` must be called after `setApplicationName`
        // see https://discuss.newrelic.com/t/newrelic-ignore-transaction-not-being-honored/5450/5
        if (in_array($command->getName(), $this->ignoredCommands)) {
            $this->interactor->ignoreTransaction();
        }

        $this->interactor->enableBackgroundJob();

        // send parameters to New Relic
        foreach ($input->getOptions() as $key => $value) {
            $key = '--' . $key;
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomParameter($key . '[' . $k . ']', $v);
                }
            } else {
                $this->interactor->addCustomParameter($key, $value);
            }
        }

        foreach ($input->getArguments() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomParameter($key . '[' . $k . ']', $v);
                }
            } else {
                $this->interactor->addCustomParameter($key, $value);
            }
        }
    }

    /**
     * @param ConsoleErrorEvent $event
     */
    public function onConsoleError(ConsoleErrorEvent $event)
    {
        $this->interactor->noticeThrowable($event->getError());
    }
}
