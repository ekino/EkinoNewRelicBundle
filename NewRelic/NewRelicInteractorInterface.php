<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

interface NewRelicInteractorInterface
{
    /**
     * @param string $name
     *
     * @return void
     */
    function setApplicationName($name);

    /**
     * @param string $name
     *
     * @return void
     */
    function setTransactionName($name);

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    function addCustomMetric($name, $value);

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    function addCustomParameter($name, $value);
}