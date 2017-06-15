<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Ekino\Bundle\NewRelicBundle\Exception;

/**
 * Encapsulate a \Throwable into an \Exception
 */
class ThrowableException extends \ErrorException
{
    public function __construct(\Throwable $throwable)
    {
        parent::__construct($throwable->getMessage(), $throwable->getCode(), 1, $throwable->getFile(), $throwable->getLine(), $throwable);
    }
}
