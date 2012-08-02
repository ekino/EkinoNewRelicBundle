<?php

namespace Ekino\Bundle\NewRelicBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ekino\Bundle\NewRelicBundle\DependencyInjection\Compiler\NewRelicCompilerPass;

class EkinoNewRelicBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NewRelicCompilerPass());
    }
}
