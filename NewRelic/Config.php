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

namespace Ekino\NewRelicBundle\NewRelic;

/**
 * This value object contains data and configuration that should be passed to the interactors.
 */
class Config
{
    private $name;
    private $apiKey;
    private $licenseKey;
    private $xmit;
    private $customEvents;
    private $customMetrics;
    private $customParameters;
    private $deploymentNames;

    public function __construct(?string $name, string $apiKey = null, string $licenseKey = null, bool $xmit = false, array $deploymentNames = [])
    {
        $this->name = !empty($name) ? $name : \ini_get('newrelic.appname') ?: '';
        $this->apiKey = $apiKey;
        $this->licenseKey = !empty($licenseKey) ? $licenseKey : \ini_get('newrelic.license') ?: '';
        $this->xmit = $xmit;
        $this->deploymentNames = $deploymentNames;
        $this->customEvents = [];
        $this->customMetrics = [];
        $this->customParameters = [];
    }

    public function setCustomEvents(array $customEvents): void
    {
        $this->customEvents = $customEvents;
    }

    public function getCustomEvents(): array
    {
        return $this->customEvents;
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->customEvents[$name][] = $attributes;
    }

    public function setCustomMetrics(array $customMetrics): void
    {
        $this->customMetrics = $customMetrics;
    }

    public function getCustomMetrics(): array
    {
        return $this->customMetrics;
    }

    public function setCustomParameters(array $customParameters): void
    {
        $this->customParameters = $customParameters;
    }

    /**
     * @param string           $name
     * @param string|int|float $value or any scalar value
     */
    public function addCustomParameter(string $name, $value): void
    {
        $this->customParameters[$name] = $value;
    }

    public function addCustomMetric(string $name, float $value): void
    {
        $this->customMetrics[$name] = $value;
    }

    public function getCustomParameters(): array
    {
        return $this->customParameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getDeploymentNames(): array
    {
        return $this->deploymentNames;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getLicenseKey(): ?string
    {
        return $this->licenseKey;
    }

    public function getXmit(): bool
    {
        return $this->xmit;
    }
}
