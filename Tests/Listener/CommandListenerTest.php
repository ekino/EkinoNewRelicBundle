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

namespace Ekino\NewRelicBundle\Tests\Listener;

use Ekino\NewRelicBundle\Listener\CommandListener;
use Ekino\NewRelicBundle\NewRelic\Config;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandListenerTest extends TestCase
{
    public function testCommandMarkedAsBackgroundJob()
    {
        if (!\class_exists('Symfony\Component\Console\Event\ConsoleCommandEvent')) {
            $this->markTestSkipped('Console Events is only available from Symfony 2.3');
        }

        $parameters = [
            '--foo' => true,
            '--foobar' => ['baz', 'baz_2'],
            'name' => 'bar',
        ];

        $definition = new InputDefinition([
            new InputOption('foo'),
            new InputOption('foobar', 'fb', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputArgument('name', InputArgument::REQUIRED),
         ]);

        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('setTransactionName')->with($this->equalTo('test:newrelic'));
        $interactor->expects($this->once())->method('enableBackgroundJob');

        $interactor->expects($this->exactly(4))->method('addCustomParameter')->withConsecutive(
            ['--foo', true],
            ['--foobar[0]', 'baz'],
            ['--foobar[1]', 'baz_2'],
            ['name', 'bar']
        );

        $command = new Command('test:newrelic');
        $input = new ArrayInput($parameters, $definition);

        $output = $this->getMockBuilder(OutputInterface::class)->getMock();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener(new Config('App name', 'Token'), $interactor, []);
        $listener->onConsoleCommand($event);
    }

    public function testIgnoreBackgroundJob()
    {
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->never())->method('startTransaction');

        $command = new Command('test:ignored-commnand');
        $input = new ArrayInput([], new InputDefinition([]));

        $output = $this->getMockBuilder(OutputInterface::class)->getMock();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener = new CommandListener(new Config('App name', 'Token'), $interactor, ['test:ignored-command']);
        $listener->onConsoleCommand($event);
    }

    public function testConsoleError()
    {
        $exception = new \Exception('', 1);

        $newrelic = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable')->with($exception);

        $command = new Command('test:exception');

        $input = new ArrayInput([], new InputDefinition([]));
        $output = $this->getMockBuilder(OutputInterface::class)->getMock();

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $listener = new CommandListener($newrelic, $interactor, ['test:exception']);
        $listener->onConsoleError($event);
    }

    public function testConsoleErrorsWithThrowable()
    {
        $exception = new \Error();

        $newrelic = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $interactor = $this->getMockBuilder(NewRelicInteractorInterface::class)->getMock();
        $interactor->expects($this->once())->method('noticeThrowable')->with($exception);
        $command = new Command('test:exception');

        $input = new ArrayInput([], new InputDefinition([]));
        $output = $this->getMockBuilder(OutputInterface::class)->getMock();

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $listener = new CommandListener($newrelic, $interactor, ['test:exception']);
        $listener->onConsoleError($event);
    }
}
