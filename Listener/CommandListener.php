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
        $command = $event->getCommand();
        $input = $event->getInput();

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