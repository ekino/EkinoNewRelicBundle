<?php

namespace Ekino\Bundle\NewRelicBundle\Tests\Functional\Bundle\AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class FooController extends ContainerAware
{
    public function fooAction()
    {
        return new Response();
    }
}
