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
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandListener implements EventSubscriberInterface
{
    private $interactor;
    private $config;
    private $ignoredCommands;

    public function __construct(Config $config, NewRelicInteractorInterface $interactor, array $ignoredCommands)
    {
        $this->config = $config;
        $this->interactor = $interactor;
        $this->ignoredCommands = $ignoredCommands;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleCommand', 0],
            ConsoleEvents::ERROR => ['onConsoleError', 0],
        ];
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $input = $event->getInput();

        if ($this->config->getName()) {
            $this->interactor->setApplicationName($this->config->getName(), $this->config->getLicenseKey(), $this->config->getXmit());
        }
        $this->interactor->setTransactionName($command->getName());

        // Due to newrelic's extension implementation, the method `ignoreTransaction` must be called after `setApplicationName`
        // see https://discuss.newrelic.com/t/newrelic-ignore-transaction-not-being-honored/5450/5
        if (in_array($command->getName(), $this->ignoredCommands, true)) {
            $this->interactor->ignoreTransaction();
        }

        $this->interactor->enableBackgroundJob();

        // send parameters to New Relic
        foreach ($input->getOptions() as $key => $value) {
            $key = '--'.$key;
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomParameter($key.'['.$k.']', $v);
                }
            } else {
                $this->interactor->addCustomParameter($key, $value);
            }
        }

        foreach ($input->getArguments() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomParameter($key.'['.$k.']', $v);
                }
            } else {
                $this->interactor->addCustomParameter($key, $value);
            }
        }
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $this->interactor->noticeThrowable($event->getError());
    }
}
