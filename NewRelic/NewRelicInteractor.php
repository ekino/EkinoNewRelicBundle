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
        if (extension_loaded('newrelic') && function_exists('newrelic_set_appname')) {
            newrelic_set_appname($name, $key, $xmit);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_name_transaction')) {
            newrelic_name_transaction($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction ()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_ignore_transaction')) {
            newrelic_ignore_transaction();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_custom_metric')) {
            newrelic_custom_metric((string)$name, (double)$value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter((string)$name, (string)$value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_get_browser_timing_header')) {
            return newrelic_get_browser_timing_header();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_get_browser_timing_footer')) {
            return newrelic_get_browser_timing_footer();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_disable_autorum')) {
            newrelic_disable_autorum();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_notice_error')) {
            newrelic_notice_error($msg);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_notice_error')) {
            newrelic_notice_error(null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_background_job')) {
            newrelic_background_job(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_background_job')) {
            newrelic_background_job(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction()
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_end_transaction')) {
            newrelic_end_transaction(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction($name)
    {
        if (extension_loaded('newrelic') && function_exists('newrelic_start_transaction')) {
            newrelic_start_transaction($name);
        }
    }
}
