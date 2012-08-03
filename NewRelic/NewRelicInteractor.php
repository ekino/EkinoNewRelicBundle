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

class NewRelicInteractor implements NewRelicInteractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
        newrelic_set_appname($name);
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
    public function addCustomMetric($name, $value)
    {
        newrelic_custom_metric((string) $name, (double) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        newrelic_custom_parameter((string) $name, (string) $value);
    }
}