<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\NewRelic;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;

class NewRelicTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneric()
    {

        $newRelic = new NewRelic('Ekino', 'XXX');

        $this->assertEquals('Ekino', $newRelic->getName());
        $this->assertEquals('XXX', $newRelic->getApiKey());

        $this->assertEmpty($newRelic->getCustomMetrics());
        $this->assertEmpty($newRelic->getCustomParameters());

        $newRelic->addCustomMetric('foo', 'bar');
        $newRelic->addCustomMetric('asd', 1);

        $expected = array(
            'foo' => 0,
            'asd' => 1
        );

        $this->assertEquals($expected, $newRelic->getCustomMetrics());

        $newRelic->addCustomParameter('param1', 1);

        $expected = array(
            'param1' => '1'
        );

        $this->assertEquals($expected, $newRelic->getCustomParameters());
    }
}