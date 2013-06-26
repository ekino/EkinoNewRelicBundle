<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Listener;

use Ekino\Bundle\NewRelicBundle\Listener\CommandListener;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

class CommandListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandMarkedAsBackgroundJob()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleCommandEvent')) {
            $this->markTestSkipped('Console Events is only available from Symfony 2.3');
        }

        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('enableBackgroundJob');

        $command = new Command('test:newrelic');
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener($interactor);
        $listener->onConsoleCommand($event);
    }
}