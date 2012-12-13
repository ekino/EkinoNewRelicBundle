<?php

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
