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
 * This interactor does never assume that the NewRelic extension is installed. It will check
 * for the existence of each method EVERY time. This is a good interactor to use when you want
 * to enable and disable the NewRelic extension without rebuilding your container.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AutoInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name, $key = null, $xmit = false)
    {
        if (function_exists('newrelic_set_appname')) {
            newrelic_set_appname($name, $key, $xmit);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        if (function_exists('newrelic_name_transaction')) {
            newrelic_name_transaction($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreTransaction ()
    {
        if (function_exists('newrelic_ignore_transaction')) {
            newrelic_ignore_transaction();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        if (function_exists('newrelic_custom_metric')) {
            newrelic_custom_metric((string) $name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter((string) $name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        if (function_exists('newrelic_get_browser_timing_header')) {
            return newrelic_get_browser_timing_header();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        if (function_exists('newrelic_get_browser_timing_footer')) {
            return newrelic_get_browser_timing_footer();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
        if (function_exists('newrelic_disable_autorum')) {
            return newrelic_disable_autorum();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
        if (function_exists('newrelic_notice_error')) {
            return newrelic_notice_error($msg);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
        if (function_exists('newrelic_notice_error')) {
            return newrelic_notice_error(null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enableBackgroundJob()
    {
        if (function_exists('newrelic_background_job')) {
            return newrelic_background_job(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disableBackgroundJob()
    {
        if (function_exists('newrelic_background_job')) {
            return newrelic_background_job(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction()
    {
        if (function_exists('newrelic_end_transaction')) {
            return newrelic_end_transaction(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction($name)
    {
        if (function_exists('newrelic_start_transaction')) {
            return newrelic_start_transaction($name);
        }
    }
}
