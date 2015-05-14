<?php

namespace Ekino\Bundle\NewRelicBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * App Test Kernel for functional tests.
 */
class AppKernel extends Kernel
{
    private $config;

    public function __construct($config)
    {
        parent::__construct('test', true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__.'/config/'.$config;
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Ekino\Bundle\NewRelicBundle\EkinoNewRelicBundle(),
            new \Ekino\Bundle\NewRelicBundle\Tests\Functional\Bundle\AppBundle\AppBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/EkinoNewRelicBundle/cache/'.sha1($this->config);
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/EkinoNewRelicBundle/logs';
    }

    protected function getContainerClass()
    {
        return parent::getContainerClass().sha1($this->config);
    }

    public function serialize()
    {
        return $this->config;
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }
}
