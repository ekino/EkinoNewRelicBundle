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

namespace Ekino\NewRelicBundle\Tests;

use Ekino\NewRelicBundle\EkinoNewRelicBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;

class AppKernel extends Kernel
{
    /**
     * @var string
     */
    private $cachePrefix = '';

    /**
     * @var string|null;
     */
    private $fakedProjectDir;

    /**
     * @param string $cachePrefix
     */
    public function __construct($cachePrefix)
    {
        parent::__construct($cachePrefix, true);
        $this->cachePrefix = $cachePrefix;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/ekino/'.$this->cachePrefix;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/ekino/log';
    }

    public function getProjectDir(): string
    {
        if (null === $this->fakedProjectDir) {
            return realpath(__DIR__.'/../../../../');
        }

        return $this->fakedProjectDir;
    }

    /**
     * @param string|null $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param string|null $projectDir
     */
    public function setProjectDir($projectDir)
    {
        $this->fakedProjectDir = $projectDir;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new EkinoNewRelicBundle(),
        ];
    }

    /**
     * (From MicroKernelTrait)
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'router' => [
                    'resource' => 'kernel:loadRoutes',
                    'type' => 'service',
                ],
                'http_method_override' => false,
            ]);

            // Not setting the router to utf8 is deprecated in symfony 5.1
            if (Kernel::VERSION_ID >= 50100) {
                $container->loadFromExtension('framework', [
                    'router' => ['utf8' => true],
                ]);
            }

            // Not setting the "framework.session.storage_factory_id" configuration option is deprecated in symfony 5.3
            if (Kernel::VERSION_ID >= 50300) {
                $container->loadFromExtension('framework', [
                    'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
                ]);
            } else {
                $container->loadFromExtension('framework', [
                    'session' => ['storage_id' => 'session.storage.mock_file'],
                ]);
            }

            $container->addObjectResource($this);
        });
    }

    /**
     * (From MicroKernelTrait).
     *
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader)
    {
        return new RouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    if (preg_match('|Ekino.*|i', $id)) {
                        $definition->setPublic(true);
                    }
                }

                foreach ($container->getAliases() as $id => $alias) {
                    if (preg_match('|Ekino.*|i', $id)) {
                        $alias->setPublic(true);
                    }
                }
            }
        });

        return $container;
    }

    /**
     * @throws \Exception
     */
    public function __serialize(): array
    {
        return unserialize($this->serialize());
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize(serialize($data));
    }
}
