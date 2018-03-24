<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Magnus Nordlander
 */
class RouteNamingStrategy implements TransactionNamingStrategyInterface
{
    public function getTransactionName(Request $request)
    {
        return $request->get('_route') ?: 'Unknown Symfony route';
    }
}
