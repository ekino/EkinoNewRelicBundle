<?php

namespace Ekino\Bundle\NewRelicBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ekino\Bundle\NewRelicBundle\Tests\Functional\app\AppKernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListenerTest extends WebTestCase
{
    const EVENT_LISTENER_CLASS = 'Ekino\Bundle\NewRelicBundle\Listener\RequestListener';

    const EVENT_LISTENER_METHOD = 'onCoreRequest';

    const EVENT_LISTENER_PRIORITY = 31;

    /**
     * Tests that the request listener is called for a given request url
     *
     * @dataProvider urlProvider
     */
    public function testIsCalled($url)
    {
        $kernel = self::createKernel();
        $kernel->boot();

        // Remove new relic listener
        $dispatcher = $kernel->getContainer()->get('event_dispatcher');
        $listener = $this->findNewRelicRequestListener($dispatcher);
        $dispatcher->removeListener(KernelEvents::REQUEST, $listener);

        // Create mock of new relic kernel.request listener
        $listener = $this->getMockBuilder($this::EVENT_LISTENER_CLASS)
            ->disableOriginalConstructor()
            ->getMock();

        // Assert that the NewRelic request listener is called
        $listener
            ->expects($this->atLeastOnce())
            ->method($this::EVENT_LISTENER_METHOD);

        // Add new relic mock listener
        $dispatcher->addListener(
            KernelEvents::REQUEST,
            array($listener, $this::EVENT_LISTENER_METHOD),
            $this::EVENT_LISTENER_PRIORITY
        );

        $request = Request::create($url);
        $kernel->handle($request);
    }

    public function urlProvider()
    {
        return array(
            array('/no-authentication'),
            array('/authentication'),
        );
    }

    protected static function createKernel(array $options = array())
    {
        return new AppKernel(
            isset($options['config']) ? $options['config'] : 'config_test.yml'
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/EkinoNewRelicBundle');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/EkinoNewRelicBundle');
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     *
     * @return callable
     */
    private function findNewRelicRequestListener(EventDispatcherInterface $dispatcher)
    {
        $listeners = $dispatcher->getListeners(KernelEvents::REQUEST);

        foreach ($listeners as $listener) {
            if (!is_array($listener)) {
                return;
            }

            $class = $listener[0];
            $method = $listener[1];

            if (get_class($class) === $this::EVENT_LISTENER_CLASS && $method === $this::EVENT_LISTENER_METHOD) {
                return $listener;
            }
        }
    }
}
