<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Logging;

use Monolog\Handler\NewRelicHandler;
use Psr\Log\LogLevel;

class AdaptiveHandler extends NewRelicHandler
{
    public function __construct(
        $level = LogLevel::ERROR,
        $bubble = true,
        $appName = null,
        $explodeArrays = false,
        $transactionName = null
    ) {
        parent::__construct($level, $bubble, $appName, $explodeArrays, $transactionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if (!$this->isNewRelicEnabled()) {
            return;
        }

        return parent::write($record);
    }
}
