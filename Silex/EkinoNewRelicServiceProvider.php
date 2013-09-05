<?php

namespace Ekino\Bundle\NewRelicBundle\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\HttpKernel\KernelEvents;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractor;
use Ekino\Bundle\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;
use Ekino\Bundle\NewRelicBundle\Listener\ExceptionListener;
use Ekino\Bundle\NewRelicBundle\Listener\ResponseListener;
use Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\RouteNamingStrategy;

/**
 * @author  Jérôme TAMARELLE <jerome@tamarelle.net>
 */
class EkinoNewRelicServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['new_relic.enabled'] = function_exists('newrelic_name_transaction');
        $app['new_relic.application_name'] = 'Silex Application';
        $app['new_relic.logging'] = false;
        $app['new_relic.log_exceptions'] = false;
        $app['new_relic.log_commands'] = false;

        $app['new_relic'] = $app->share(function ($app) {
            return new NewRelic();
        });

        $app['new_relic.transaction_naming_strategy'] = $app->share(function ($app) {
            // The route strategy is used by default because closures controllers
            // cannot be represented by a string
            return new RouteNamingStrategy();
        });

        $app['new_relic.interactor'] = $app->share(function ($app) {
            if ($app['new_relic.logging']) {
                return $app['new_relic.interactor.logger'];
            }

            return $app['new_relic.interactor.real'];
        })

        $app['new_relic.interactor.real'] = $app->share(function ($app) {
            return new NewRelicInteractor();
        });

        $app['new_relic.interactor.logger'] = $app->share(function ($app) {
            return new LoggingInteractorDecorator($app['new_relic.interactor.real'], isset($app['logger']) ? $app['logger'] : null)
        });

        $app['new_relic.request_listener'] = $app->share(function ($app) {
            return new RequestListener($app['new_relic'], $app['new_relic.interactor'], array(), array(), $app['new_relic.transaction_naming_strategy']);
        });
        $app['new_relic.exception_listener'] = $app->share(function ($app) {
            return new ResponseListener($app['new_relic.interactor']);
        });
        $app['new_relic.response_listener'] = $app->share(function ($app) {
            return new ResponseListener($app['new_relic'], $app['new_relic.interactor'], false);
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
    }
}
