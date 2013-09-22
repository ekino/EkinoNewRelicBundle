<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Silex;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ekino\Bundle\NewRelicBundle\Silex\EkinoNewRelicServiceProvider;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Validate
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class EkinoNewRelicServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestListener()
    {
        $app = $this->createApplication();

        $app['new_relic.interactor.real']
            ->expects($this->once())
            ->method('setApplicationName')
            ->with($this->equalTo('Silex Application'))
        ;

        $app['new_relic.interactor.real']
            ->expects($this->once())
            ->method('setTransactionName')
            ->with($this->equalTo('my_page'))
        ;

        $response = $app->handle(Request::create('/page'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Test Exception
     */
    public function testExceptionListener()
    {
        $app = $this->createApplication();

        $app['new_relic.log_exceptions'] = true;

        $app['new_relic.interactor.real']
            ->expects($this->once())
            ->method('noticeException')
        ;

        $response = $app->handle(Request::create('/error'));
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Test Exception
     */
    public function testExceptionListenerDisabledByDefault()
    {
        $app = $this->createApplication();

        $this->assertFalse($app['new_relic.log_exceptions']);

        $app['new_relic.interactor.real']
            ->expects($this->never())
            ->method('noticeException')
        ;

        $response = $app->handle(Request::create('/error'));
    }

    public function testConsoleListener()
    {
        $app = $this->createApplication();

        $app['new_relic.interactor.real']
            ->expects($this->once())
            ->method('setTransactionName')
            ->with($this->equalTo('my-command'))
        ;

        $app['new_relic.interactor.real']
            ->expects($this->once())
            ->method('enableBackgroundJob')
        ;

        $app->boot();

        $event = new ConsoleCommandEvent(
            new Command('my-command'),
            new ArrayInput(array()),
            new NullOutput()
        );

        $app['dispatcher']->dispatch(ConsoleEvents::COMMAND, $event);
    }

    public function testCommandRegistered()
    {
        $app = $this->createApplication();

        $this->assertInstanceOf('Symfony\\Component\\Console\\Command\\Command', $app['new_relic.command.notify']);
    }

    private function createApplication()
    {
        $app = new Application();
        unset($app['exception_handler']);

        $app->register(new EkinoNewRelicServiceProvider(), array(
            'new_relic.enabled' => true,
        ));

        $app['new_relic.interactor.real'] = $this->getMock('Ekino\\Bundle\\NewRelicBundle\\NewRelic\\NewRelicInteractor');

        $app->get('/page', function () {
            return 'OK';
        })->bind('my_page');

        $app->get('/error', function () {
            throw new \RuntimeException('Test Exception');
        })->bind('my_error');

        return $app;
    }
}
