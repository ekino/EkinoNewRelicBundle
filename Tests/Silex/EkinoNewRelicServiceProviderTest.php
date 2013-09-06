<?php

namespace Ekino\Bundle\NewRelicBundle\Tests\Silex;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ekino\Bundle\NewRelicBundle\Silex\EkinoNewRelicServiceProvider;

class EkinoNewRelicServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testListenerCalled()
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

    public function createApplication()
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
            throw new \RuntimeException('test');
        })->bind('my_error');

        return $app;
    }
}