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
use PHPUnit\Framework\TestCase;

class NewRelicTest extends TestCase
{
    public function testGeneric()
    {

        $newRelic = new NewRelic('Ekino', 'XXX');

        $this->assertEquals('Ekino', $newRelic->getName());
        $this->assertEquals('XXX', $newRelic->getApiKey());

        $this->assertEmpty($newRelic->getCustomEvents());
        $this->assertEmpty($newRelic->getCustomMetrics());
        $this->assertEmpty($newRelic->getCustomParameters());

        $newRelic->addCustomEvent('WidgetSale', array('color' => 'red', 'weight' => 12.5));
        $newRelic->addCustomEvent('WidgetSale', array('color' => 'blue', 'weight' => 12.5));

        $expected = array(
            'WidgetSale' => array(
                array(
                    'color' => 'red',
                    'weight' => 12.5,
                ),
                array(
                    'color' => 'blue',
                    'weight' => 12.5,
                ),
            ),
        );

        $this->assertEquals($expected, $newRelic->getCustomEvents());

        $newRelic->addCustomMetric('foo', 4.2);
        $newRelic->addCustomMetric('asd', 1);

        $expected = array(
            'foo' => 4.2,
            'asd' => 1
        );

        $this->assertEquals($expected, $newRelic->getCustomMetrics());

        $newRelic->addCustomParameter('param1', 1);

        $expected = array(
            'param1' => '1'
        );

        $this->assertEquals($expected, $newRelic->getCustomParameters());
    }

    public function testDefaults()
    {
        $newRelic = new NewRelic(null, null);

        $this->assertNotNull($newRelic->getName());
        $this->assertEquals(ini_get('newrelic.appname'), $newRelic->getName());

        $this->assertNotNull($newRelic->getLicenseKey());
        $this->assertEquals(ini_get('newrelic.license'), $newRelic->getLicenseKey());
    }
}
