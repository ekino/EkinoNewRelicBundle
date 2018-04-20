UPGRADE FROM 1.x to 2.0
=======================

> Many internal things in the bundle has changed. If you only installed the
> bundle and added some configuration then you just have to check the 2 first
> steps of this file.

 * The namespace of the bundle has changed to follow:

   Before:

   ```php
   $bundles[] = new Ekino\Bundle\NewRelicBundle\EkinoNewRelicBundle();
   ```

   After:

   ```php
   $bundles[] = new Ekino\NewRelicBundle\EkinoNewRelicBundle();
   ```

* The configuration structure has changed

  ```
  | EkinoNewRelicBundle 1.x      | EkinoNewRelicBundle 2.0
  | ---------------------------- | --------------------------------
  | ekino_new_relic:             | ekino_new_relic:
  |   enabled                    |   enabled
  |   application_name           |   application_name
  |   deployment_names           |   deployment_names
  |   api_key                    |   api_key
  |   license_key                |   license_key
  |   xmit                       |   xmit
  |   logging                    |   logging
  |   instrument                 |   instrument
  |   log_exceptions             |   exceptions
  |                              |   interactor
  |                              |   twig
  |                              |   deprecations
  |                              |   http
  |                              |     enabled
  |   using_symfony_cache        |     using_symfony_cache
  |   transaction_naming         |     transaction_naming
  |   transaction_naming_service |     transaction_naming_service
  |   ignored_routes             |     ignored_routes
  |   ignored_paths              |     ignored_paths
  |                              |   monolog
  |                              |     enabled
  |                              |     channels
  |                              |     level
  |                              |     service
  |                              |   commands
  |   log_commands               |     enabled
  |   ignored_commands           |     ignored_commands
  ```

* The Sonata integration has been removed.

* The Silex integration has been removed.

* Services are private by default. You should either use service injection
  or explicitly define your services as public if you really need to inject
  the container.

* The parameters `ekino.new_relic.interactor.real.class` and `ekino.new_relic.interactor.blackhole.class`
  have been removed. You should decorate the services instead.

* Name of services uses the class FQDN instead of string alias

  | EkinoNewRelicBundle 1.x                                  | EkinoNewRelicBundle 2.0
  | -------------------------------------------------------- | --------------------------------------------------------------------------
  | `ekino.new_relic.command_listener`                       | `Ekino\NewRelicBundle\Listener\CommandListener`
  | `ekino.new_relic.exception_listener`                     | `Ekino\NewRelicBundle\Listener\ExceptionListener`
  | `ekino.new_relic.interactor`                             | `Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface`
  | `ekino.new_relic.interactor.blackhole`                   | `Ekino\NewRelicBundle\NewRelic\BlackholeInteractor`
  | `ekino.new_relic.interactor.logger`                      | `Ekino\NewRelicBundle\NewRelic\LoggingInteractorDecorator`
  | `ekino.new_relic.interactor.real`                        | `Ekino\NewRelicBundle\NewRelic\NewRelicInteractor`
  | `ekino.new_relic.request_listener`                       | `Ekino\NewRelicBundle\Listener\RequestListener`
  | `ekino.new_relic.response_listener`                      | `Ekino\NewRelicBundle\Listener\ResponseListener`
  | `ekino.new_relic.transaction_naming_strategy.controller` | `Ekino\NewRelicBundle\TransactionNamingStrategy\ControllerNamingStrategy`
  | `ekino.new_relic.transaction_naming_strategy.route`      | `Ekino\NewRelicBundle\TransactionNamingStrategy\RouteNamingStrategy`
  | `ekino.new_relic.twig.new_relic_extension`               | `Ekino\NewRelicBundle\Twig\NewRelicExtension`
  | `ekino.new_relic`                                        | `Ekino\NewRelicBundle\NewRelic\Config`


* The `NewRelicInteractorInterface` changed.

  * added scalar type hinting on method declaration
  * added method `addCustomEvent`
  * added method `addCustomTracer`
  * added method `excludeFromApdex`
  * added method `recordDatastoreSegment`
  * added method `setCaptureParams`
  * added method `setUserAttributes`
  * added method `stopTransactionTiming`
  * rename method `noticeException` to `noticeThrowable`
  * added parameter `$ignore` to the method `endTransaction`
  * added parameters `$errno`, `$errstr`, `$errfile`, `$errline`, `$errcontext` to the method `noticeError`
  * added parameter `$license` to the method `startTransaction`

* The `TransactionNamingStrategyInterface` changed.

  * Added scalar type hinting on method declaration

* Allmost all classes changed to add scalar type hinting and protected methods have been removed, you should use composition over inheritance.

* The class `NewRelic\NewRelic` has been renamed to `NewRelic\Config`
