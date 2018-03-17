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
 * This value object contains data that should be passed to the interactors.
 */
class NewRelic
{
    protected $name;

    protected $apiKey;

    protected $licenseKey;

    protected $xmit;

    protected $framework;

    protected $customMetrics;

    protected $customParameters;

    protected $deploymentNames;

    /**
     * @param string  $name
     * @param string  $apiKey
     * @param string  $licenseKey
     * @param boolean $xmit
     * @param array   $deploymentNames
     */
    public function __construct($name, $apiKey, $licenseKey = null, $xmit = false, array $deploymentNames = array())
    {
        $this->name             = $name ?: ini_get('newrelic.appname');
        $this->apiKey           = $apiKey;
        $this->licenseKey       = $licenseKey ?: ini_get('newrelic.license');
        $this->xmit             = $xmit;
        $this->deploymentNames  = $deploymentNames;
        $this->customMetrics    = array();
        $this->customParameters = array();
    }

    /**
     * @param array $customMetrics
     */
    public function setCustomMetrics(array $customMetrics)
    {
        $this->customMetrics = $customMetrics;
    }

    /**
     * @return array
     */
    public function getCustomMetrics()
    {
        return $this->customMetrics;
    }

    /**
     * @param array $customParameters
     */
    public function setCustomParameters(array $customParameters)
    {
        $this->customParameters = $customParameters;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addCustomParameter($name, $value)
    {
        $this->customParameters[(string) $name] = (string) $value;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addCustomMetric($name, $value)
    {
        $this->customMetrics[(string) $name] = (double) $value;
    }

    /**
     * @return array
     */
    public function getCustomParameters()
    {
        return $this->customParameters;
    }

    /**
     * @param string $framework
     */
    public function setFramework($framework)
    {
        if (!in_array($framework, self::getFrameworksList())) {
            $framework = "no_framework";
        }

        $this->framework = $framework;
    }

    /**
     * @return string
     */
    public function getFramework()
    {
        return $this->framework;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getDeploymentNames()
    {
        return $this->deploymentNames;
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getFrameworksList()
    {
        return array(
            "cakephp",
            "codeigniter",
            "drupal",
            "joomla",
            "kohana",
            "magento",
            "mediawiki",
            "symfony",
            "wordpress",
            "yii",
            "zend",
        );
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @return string
     */
    public function getXmit()
    {
        return $this->xmit;
    }
}
