EkinoNewRelicServiceProvider
============================

The *EkinoNewRelicServiceProvider* integrates the NewRelic PHP API into Silex.

Key features:

* Application name
* Transaction name using the route name by default)
* Exception notification for controllers and commands
* Custom metrics and parameters

Parameters
----------

* **new_relic.enabled**: Check if the newrelic extension is installed by default.
* **new_relic.application_name**: .
* **new_relic.api_key**: New Relic API for the deploy command.
* **new_relic.logging**: If true, logs all New Relic interactions to the logger service.
* **new_relic.log_exceptions**: If true, sends exceptions to New Relic.
* **new_relic.log_commands**: If true, logs CLI commands to New Relic as Background jobs (symfony 2.3+ only).

Services
--------

* **new_relic**: NewRelic instance for custom metrics and parameters.
* **new_relic.interactor**: Interface for the New Relic API.
* **new_relic.command.notify**: Console command to notify NewRelic on deployments.

Registering
-----------

.. code-block:: php

    use Ekino\Bundle\NewRelicBundle\Silex\EkinoNewRelicServiceProvider;

    $app->register(new EkinoNewRelicServiceProvider(), array(
        'new_relic.application_name' => 'My Silex Application',
        'new_relic.api_key'          => 'REPLACE_WITH_YOUR_API_KEY',
        'new_relic.log_exceptions'   => true,
    ));

.. note::

    The PHP extension "newrelic" must be installed on the server.


Notify NewRelic on deploy
-------------------------

New Relic allows you to send information about application deployments.
To use the deploy command, you can simply register it to a Console Application.

.. code-block:: php

    # bin/console

    use Symfony\Component\Console\Application;

    // Load your $app

    $console = new Application();
    $console->add($app['new_relic.command.deploy']);
    $console->run();


.. note::

    You can use LExpress/ConsoleServiceProvider to register the command automatically.

Then, the command can be executed::

    php bin/console newrelic:notify-deployment --user=me
