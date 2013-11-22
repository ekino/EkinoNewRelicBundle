<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Console\ConsoleEvents;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractor;
use Ekino\Bundle\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;
use Ekino\Bundle\NewRelicBundle\Listener\ResponseListener;
use Ekino\Bundle\NewRelicBundle\Listener\ExceptionListener;
use Ekino\Bundle\NewRelicBundle\Listener\CommandListener;
use Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\RouteNamingStrategy;
use Ekino\Bundle\NewRelicBundle\Command\NotifyDeploymentCommand;

/**
 * Service Provider for Silex
 *
 * @author Jérôme TAMARELLE <jerome@tamarelle.net>
 */
class EkinoNewRelicServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['new_relic.enabled'] = extension_loaded('newrelic');
        $app['new_relic.application_name'] = 'Silex Application';
        $app['new_relic.api_key'] = null;
        $app['new_relic.logging'] = false;
        $app['new_relic.log_exceptions'] = false;
        $app['new_relic.log_commands'] = class_exists('Symfony\\Component\\Console\\ConsoleEvents');

        $app['new_relic'] = $app->share(function ($app) {
            return new NewRelic($app['new_relic.application_name'], $app['new_relic.api_key']);
        });

        $app['new_relic.transaction_naming_strategy'] = $app->share(function ($app) {
            // The route strategy is used by default because closures controllers
            // cannot be represented by a string
            return new RouteNamingStrategy();
        });

        // Interactor
        $app['new_relic.interactor'] = $app->share(function ($app) {
            if ($app['new_relic.logging']) {
                return $app['new_relic.interactor.logger'];
            }

            return $app['new_relic.interactor.real'];
        });
        $app['new_relic.interactor.real'] = $app->share(function ($app) {
            return new NewRelicInteractor();
        });
        $app['new_relic.interactor.logger'] = $app->share(function ($app) {
            return new LoggingInteractorDecorator($app['new_relic.interactor.real'], isset($app['logger']) ? $app['logger'] : null);
        });

        // Listeners
        $app['new_relic.request_listener'] = $app->share(function ($app) {
            return new RequestListener($app['new_relic'], $app['new_relic.interactor'], array(), array(), $app['new_relic.transaction_naming_strategy']);
        });
        $app['new_relic.exception_listener'] = $app->share(function ($app) {
            return new ExceptionListener($app['new_relic.interactor']);
        });
        $app['new_relic.response_listener'] = $app->share(function ($app) {
            return new ResponseListener($app['new_relic'], $app['new_relic.interactor'], false);
        });
        $app['new_relic.console_listener'] = $app->share(function ($app) {
            return new CommandListener($app['new_relic'], $app['new_relic.interactor'], array());
        });

        // Optional command
        $app['new_relic.command.notify'] = $app->share(function ($app) {
            return new NotifyDeploymentCommand($app['new_relic']);
        });
    }

    public function boot(Application $app)
    {
        if (!$app['new_relic.enabled']) {
            return;
        }

        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($app['new_relic.request_listener'], 'onCoreRequest'), -1);

        $app['dispatcher']->addListener(KernelEvents::RESPONSE, array($app['new_relic.response_listener'], 'onCoreResponse'), -1);

        if ($app['new_relic.log_exceptions']) {
            $app['dispatcher']->addListener(KernelEvents::EXCEPTION, array($app['new_relic.exception_listener'], 'onKernelException'), 0);
        }

        if ($app['new_relic.log_commands']) {
            $app['dispatcher']->addListener(ConsoleEvents::COMMAND, array($app['new_relic.console_listener'], 'onConsoleCommand'), 0);
        }

        if ($app['new_relic.log_exceptions'] && $app['new_relic.log_commands']) {
            $app['dispatcher']->addListener(ConsoleEvents::EXCEPTION, array($app['new_relic.exception_listener'], 'onKernelException'), 0);
        }
    }
}
