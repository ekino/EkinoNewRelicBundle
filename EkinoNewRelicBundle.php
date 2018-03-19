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

use Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler\MonologHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EkinoNewRelicBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MonologHandlerPass());
    }

    public function boot()
    {
        parent::boot();

        if ($this->container->getParameter('ekino.new_relic.log_deprecations')) {
            $this->container->get('ekino.new_relic.deprecation_listener')->register();
        }
    }
}

