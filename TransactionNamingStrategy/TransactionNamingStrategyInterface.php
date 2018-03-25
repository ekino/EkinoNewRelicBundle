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

namespace Ekino\NewRelicBundle\TransactionNamingStrategy;

use Symfony\Component\HttpFoundation\Request;

interface TransactionNamingStrategyInterface
{
    public function getTransactionName(Request $request): string;
}
