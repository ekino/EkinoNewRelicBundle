<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Listener;

use Ekino\Bundle\NewRelicBundle\Listener\CommandListener;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

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

        $interactor->expects($this->at(3))->method('addCustomParameter')->with('--foo', true);
        $interactor->expects($this->at(4))->method('addCustomParameter')->with('--foobar[0]', 'baz');
        $interactor->expects($this->at(5))->method('addCustomParameter')->with('--foobar[1]', 'baz_2');
        $interactor->expects($this->at(6))->method('addCustomParameter')->with('name', 'bar');

        $command = new Command('test:newrelic');
        $input = new ArrayInput($parameters, $definition);

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener(new NewRelic('App name', 'Token'), $interactor, array());
        $listener->onConsoleCommand($event);
    }

    public function testIgnoreBackgroundJob ()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleCommandEvent')) {
            $this->markTestSkipped('Console Events is only available from Symfony 2.3');
        }

        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');

        $command = new Command('test:ignored-commnand');
        $input = new ArrayInput(array(), new InputDefinition(array()));

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener(new NewRelic('App name', 'Token'), $interactor, array('test:ignored-command'));
        $listener->onConsoleCommand($event);
    }

    public function testConsoleException()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleExceptionEvent')) {
            $this->markTestSkipped('Console Exception Events is only available under Symfony 3.3');
        }

        $exception = new \Exception;

        $newrelic = $this->getMockBuilder('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic')->disableOriginalConstructor()->getMock();
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('noticeException')->with($exception);

        $command = new Command('test:exception');

        $input = new ArrayInput(array(), new InputDefinition(array()));
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

        $listener = new CommandListener(new NewRelic('App name', 'Token'), $interactor, array('test:ignored-command'));
        $listener->onConsoleException($event);
    }

    public function testConsoleError()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')) {
            $this->markTestSkipped('Console Error Events is only available from Symfony 3.3');
        }

        $exception = new \Exception('', 1);

        $newrelic = $this->getMockBuilder('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic')->disableOriginalConstructor()->getMock();
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('noticeException')->with($exception);

        $command = new Command('test:exception');

        $input = new ArrayInput(array(), new InputDefinition(array()));
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');


        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $listener = new CommandListener($newrelic, $interactor, array('test:exception'));
        $listener->onConsoleError($event);
    }

    public function testConsoleErrorsWithThrowable()
    {
        if (!class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')) {
            $this->markTestSkipped('Console Error Events is only available from Symfony 3.3');
        }
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Error are only testable with PHP 7');
        }

        $exception = new \Error();

        $newrelic = $this->getMockBuilder('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic')->disableOriginalConstructor()->getMock();
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('noticeException')->with($this->isInstanceOf('Ekino\Bundle\NewRelicBundle\Exception\ThrowableException'));

        $command = new Command('test:exception');

        $input = new ArrayInput(array(), new InputDefinition(array()));
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $listener = new CommandListener($newrelic, $interactor, array('test:exception'));
        $listener->onConsoleError($event);
    }
}
