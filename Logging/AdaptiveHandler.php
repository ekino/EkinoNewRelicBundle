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

namespace Ekino\NewRelicBundle\Logging;

use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\NewRelicHandler;
use Psr\Log\LogLevel;

class AdaptiveHandler extends NewRelicHandler
{
    public function __construct(
        string $level = LogLevel::ERROR,
        bool $bubble = true,
        string $appName = null,
        bool $explodeArrays = false,
        string $transactionName = null
    ) {
        parent::__construct($level, $bubble, $appName, $explodeArrays, $transactionName);
    }

    /**
     * @throws MissingExtensionException
     */
    protected function write(array $record): void
    {
        if (!$this->isNewRelicEnabled()) {
            return;
        }

        parent::write($record);
    }
}
