<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\NewRelic;

/**
 * This is the service that talks to NewRelic.
 */
interface NewRelicInteractorInterface
{
    /**
     * @param string $name
     *
     * @return void
     */
    function setApplicationName($name, $key = null, $xmit = false);

    /**
     * @param string $name
     *
     * @return void
     */
    function setTransactionName($name);

    /**
     * @return void
     */
    public function ignoreTransaction();

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

    /**
     * @return string
     */
    function getBrowserTimingHeader();

    /**
     * @return string
     */
    function getBrowserTimingFooter();

    /**
     * @return void
     */
    function disableAutoRUM();

    /**
     * @param string $msg
     *
     * @return void
     */
    function noticeError($msg);

    /**
     * @param \Exception $e
     *
     * @return void
     */
    function noticeException(\Exception $e);

    /**
     * @return void
     */
    function enableBackgroundJob();

    /**
     * @return void
     */
    function disableBackgroundJob();

    /**
     * If you previously ended a transaction you many want to start a new one.
     *
     * @param string $name app name
     *
     * @return void
     */
    public function startTransaction($name);

    /**
     * End a transaction now
     *
     * @return void
     */
    public function endTransaction();
}
