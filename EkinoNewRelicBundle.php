<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;
use Ekino\Bundle\NewRelicBundle\Command\NotifyDeploymentCommand;

class EkinoNewRelicBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        $container = $application->getKernel()->getContainer();

        if ($container->has('ekino.new_relic')) {
            $newrelic = $container->get('ekino.new_relic');
            $application->add(new NotifyDeploymentCommand($newrelic));
        }
    }
}
