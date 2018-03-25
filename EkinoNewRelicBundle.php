<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\NewRelicBundle;

use Ekino\NewRelicBundle\DependencyInjection\Compiler\MonologHandlerPass;
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

        if ($this->container->has('ekino.new_relic.deprecation_listener')) {
            $this->container->get('ekino.new_relic.deprecation_listener')->register();
        }
    }
}
