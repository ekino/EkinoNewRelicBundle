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
use Ekino\NewRelicBundle\Listener\DeprecationListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EkinoNewRelicBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new MonologHandlerPass());
    }

    public function boot(): void
    {
        parent::boot();

        if ($this->container->has(DeprecationListener::class)) {
            $this->container->get(DeprecationListener::class)->register();
        }
    }

    public function shutdown(): void
    {
        if ($this->container->has(DeprecationListener::class)) {
            $this->container->get(DeprecationListener::class)->unregister();
        }

        parent::shutdown();
    }
}
