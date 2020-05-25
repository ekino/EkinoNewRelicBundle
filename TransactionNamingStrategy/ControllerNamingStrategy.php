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

/**
 * @author Magnus Nordlander
 * @author Bart van den Burg <bart@burgov.nl>
 */
class ControllerNamingStrategy implements TransactionNamingStrategyInterface
{
    public function getTransactionName(Request $request): string
    {
        $controller = $request->attributes->get('_controller');
        if (empty($controller)) {
            return 'Unknown Symfony controller';
        }

        if ($controller instanceof \Closure) {
            return 'Closure controller';
        }

        if (\is_object($controller)) {
            if (\method_exists($controller, '__invoke')) {
                return 'Callback controller: '.\get_class($controller).'::__invoke()';
            }
        }

        if (\is_callable($controller)) {
            if (\is_array($controller)) {
                if (\is_object($controller[0])) {
                    $controller[0] = \get_class($controller[0]);
                }

                $controller = \implode('::', $controller);
            }

            return 'Callback controller: '.$controller.'()';
        }

        return $controller;
    }
}
