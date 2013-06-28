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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

class CommandListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandMarkedAsBackgroundJob()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleCommandEvent')) {
            $this->markTestSkipped('Console Events is only available from Symfony 2.3');
        }

        $parameters = array(
            '--foo' => true,
            '--foobar' => array('baz', 'baz_2'),
            'name' => 'bar',
        );

        $definition = new InputDefinition(array(
            new InputOption('foo'),
            new InputOption('foobar', 'fb', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputArgument('name', InputArgument::REQUIRED),
         ));

        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('setTransactionName')->with($this->equalTo('test:newrelic'));
        $interactor->expects($this->once())->method('enableBackgroundJob');

        $interactor->expects($this->at(2))->method('addCustomParameter')->with('--foo', true);  
        $interactor->expects($this->at(3))->method('addCustomParameter')->with('--foobar[0]', 'baz');
        $interactor->expects($this->at(4))->method('addCustomParameter')->with('--foobar[1]', 'baz_2');
        $interactor->expects($this->at(5))->method('addCustomParameter')->with('name', 'bar');

        $command = new Command('test:newrelic');
        $input = new ArrayInput($parameters, $definition);

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener($interactor);
        $listener->onConsoleCommand($event);
    }
}