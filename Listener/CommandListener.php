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

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;

class CommandListener
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

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $input = $event->getInput();

        if (in_array($command->getName(), $this->ignoredCommands)) {
            $this->interactor->ignoreTransaction();
        }

        if ($this->newRelic->getName()) {
            $this->interactor->setApplicationName($this->newRelic->getName(), $this->newRelic->getLicenseKey(), $this->newRelic->getXmit());
        }
        $this->interactor->setTransactionName($command->getName());
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
}
