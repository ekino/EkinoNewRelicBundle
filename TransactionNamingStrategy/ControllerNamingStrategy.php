<?php

namespace Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy;

use Symfony\Component\HttpFoundation\Request;

/**
* @author Magnus Nordlander
*/
class ControllerNamingStrategy implements TransactionNamingStrategyInterface
{
    public function getTransactionName(Request $request)
    {
        return $request->get('_controller') ?: 'Unknown Symfony controller';
    }
}
