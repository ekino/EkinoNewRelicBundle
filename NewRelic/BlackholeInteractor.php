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

class BlackholeInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRUM()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function noticeError($msg)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function noticeException(\Exception $e)
    {
    }
}