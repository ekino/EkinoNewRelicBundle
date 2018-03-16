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

class NewRelicInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name, $key = null, $xmit = false)
    {
        newrelic_set_appname($name, $key, $xmit);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        newrelic_name_transaction($name);
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction()
    {
        newrelic_ignore_transaction();
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreApdex()
    {
        newrelic_ignore_apdex();
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        newrelic_custom_metric((string) $name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        newrelic_add_custom_parameter((string) $name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return newrelic_get_browser_timing_header();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return newrelic_get_browser_timing_footer();
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        newrelic_disable_autorum();
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        newrelic_notice_error($msg);
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        newrelic_notice_error(null, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob()
    {
        newrelic_background_job(true);
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob()
    {
        newrelic_background_job(false);
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction()
    {
        newrelic_end_transaction(false);
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction($name)
    {
        newrelic_start_transaction($name);
    }
}
