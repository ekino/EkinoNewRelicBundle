Ekino NewRelic Bundle
=====================

[![Build Status](https://secure.travis-ci.org/ekino/EkinoNewRelicBundle.png?branch=master)](http://travis-ci.org/ekino/EkinoNewRelicBundle)

This bundle integrates the NewRelic PHP API into Symfony2. For more information about NewRelic, please visit http://newrelic.com. The built-in New Relic agent doesn't add as much Symfony2 integration as it claims.  This bundle adds a lot more essentials. Here's a quick list:

1. **Better transaction naming strategy**: Your transaction traces can be named accurately by route names, the controller name or you can decide on a custom naming strategy via a seamless interface that uses any naming convention you deem fit. While running console commands, it also sets the transaction name as the command name.

2. **Console Commands Enhancements**: While running console commands, its sets the options and arguments passed via the CLI as custom parameters to the transaction trace for easier debugging.

3. **Exception Listening**: It also captures all Symfony2 exceptions in web requests and console commands and sends them to New Relic (something new relic doesn't do too well itself as symfony2 aggressively catches all exceptions/errors). It also ensures all HTTP Exceptions (4xx codes) are logged as notices in New Relic and not exceptions to reduce the noise in New Relic.

4. **Interactor Service**: It provides you the New Relic PHP Agent API via a Service class `ekino.newrelic.interactor` so in my code, I can inject it into any class, controller, service and do stuff like - 

    ```php
    // Bundle
    $this->newRelic->addCustomParameter('name', 'john');
    
    // Extension
    if (extension_loaded('newrelic')) {
        \newrelic_add_custom_parameter('name', 'john');
    }
    ```

5. **Logging Support**: In development, you are unlikely to have New Relic setup. There's a configuration to enable logging which outputs all New Relic actions to your Symfony2 log, hence emulating what it would actually do in production.

6. **Ignored Routes, Paths, Commands**: You can configure a list of route name, url paths and console commands to be ignored from New Relic traces.

    ![image](https://cloud.githubusercontent.com/assets/670655/5153003/5c956c1e-7235-11e4-9eb2-d203fa42420b.png)

7. **Misc**: There are other useful configuration like your New Relic API Key, explicitly defining your app name instead of php.ini, notifying New Relic about new deployments via capifony, etc.


![Ekino NewRelicBundle](https://dl.dropbox.com/s/bufb6f8o0end5xo/ekino_newrelic_bundle.png "Ekino NewRelicBundle")



## Installation

### Step 0 : Install NewRelic

review http://newrelic.com ...

### Step 1: Using Composer (recommended)

Use `composer.phar`:

```bash
$ php composer.phar require ekino/newrelic-bundle
```
You just have to specify the version you want : `master-dev`.
It will add the package in your `composer.json` file and install it.

Or you can do it by yourself, first, add the following to your `composer.json` file:

```js
// composer.json
{
    // ...
    require: {
        // ...
        "ekino/newrelic-bundle": "master-dev"
    }
}
```

Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

```bash
$ php composer.phar update ekino/newrelic-bundle
```

### Step 1 (alternative) : Using ``deps`` file (Symfony 2.0.x)

First, checkout a copy of the code. Just add the following to the ``deps``
file of your Symfony Standard Distribution:

```ini
[EkinoNewRelicBundle]
    git=http://github.com/ekino/EkinoNewRelicBundle.git
    target=/bundles/Ekino/Bundle/NewRelicBundle
```

Then, run

```bash
$ bin/vendors install
```

Make sure that you also register the namespace with the autoloader:

```php
<?php

// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'Ekino'              => __DIR__.'/../vendor/bundles',
    // ...
));
```

### Step 2 : Register the bundle


Then register the bundle with your kernel:

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Ekino\Bundle\NewRelicBundle\EkinoNewRelicBundle(),
    // ...
);
```

### Step 3 : Configure the bundle

``` yaml
# app/config/config.yml

ekino_new_relic:
    enabled: true                         # Defaults to true
    application_name: Awesome Application # Application name (optional, default value is read from php.ini)
    api_key:                              # New Relic API
    license_key:                          # New Relic license key (optional, default value is read from php.ini)
    xmit: false                           # if you want to record the metric data up to the point newrelic_set_appname is called, set this to true
    logging: false                        # If true, logs all New Relic interactions to the Symfony log
    instrument: false                     # If true, uses enhanced New Relic RUM instrumentation (see below)
    log_exceptions: false                 # If true, sends exceptions to New Relic
    log_commands: true                    # If true, logs CLI commands to New Relic as Background jobs (>2.3 only)
    using_symfony_cache: false            # Symfony HTTP cache (see below)
    transaction_naming: route             # route, controller or service (see below)
    transaction_naming_service: ~         # Transaction naming service (see below)
    ignored_routes: []                    # No transaction recorded for this routes
    ignored_paths: []                     # No transaction recorded for this paths
    ignored_commands: []                  # No transaction recorded for this commands (background tasks)
```

## Enhanced RUM instrumentation

The bundle comes with an option for enhanced real user monitoring. Ordinarily the New Relic extension (unless disabled by configuration) automatically adds a tracking code for RUM instrumentation to all HTML responses. Using enhanced RUM instrumentation, the bundle allows you to selectively disable instrumentation on certain requests.

This can be useful if, e.g. you're returning HTML verbatim for an HTML editor.

If enhanced RUM instrumentation is enabled, you can *disable* instrumentation for a given request by passing along a ```_instrument``` request parameter, and setting it to ```false```. This can be done e.g. through the routing configuration.

## Transaction naming strategies

The bundle comes with two built-in transaction naming strategies. ```route``` and ```controller```, naming the New Relic transaction after the route or controller respectively. However, the bundle supports custom transaction naming strategies through the ```service``` configuration option. If you have selected the ```service``` configuration option, you must pass the name of your own transaction naming service as the ```transaction_naming_service``` configuration option.

The transaction naming service class must implement the ```Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface``` interface. For more information on creating your own services, see the Symfony documentation on [Creating/Configuring Services in the Container](http://symfony.com/doc/current/book/service_container.html#creating-configuring-services-in-the-container).

## Symfony HTTP Cache

When you are using Symfony's HTTP cache your `app/AppCache.php` will build up a response with your Edge Side Includes (ESI). This will look like one transaction in New Relic. When you set `using_symfony_cache: true` will these ESI request be separate transaction which improves the statistics. If you are using some other reverse proxy cache or no cache at all, leave this to false.

If true is required to set the `application_name`.


## Deployment notification

You can use the `newrelic:notify-deployment` command to send deployment notifications to New Relic. This requires the `api_key` configuration to be set.

The command has a bunch of options, as displayed in the help data.

```
$ app/console newrelic:notify-deployment --help
Usage:
 newrelic:notify-deployment [--user[="..."]] [--revision[="..."]] [--changelog[="..."]] [--description[="..."]]

Options:
 --user         The name of the user/process that triggered this deployment
 --revision     A revision number (e.g., git commit SHA)
 --changelog    A list of changes for this deployment
 --description  Text annotation for the deployment — notes for you
```

The bundle provide a [Capifony](http://capifony.org) recipe to automate the deployment notifications (see `Resources/recipes/newrelic.rb`).

## Integration with SonataBlockBundle

### Step 0: Install SonataBlockBundle

Review [SonataBlockBundle](http://sonata-project.org/bundles/block/master/doc/reference/installation.html)

### Step 1: Enable your block:

``` yaml
# app/config/config.yml

sonata_block:
    blocks:
        ekino.newrelic.block.simple:
        ekino.newrelic.block.tabs:
```

## Integration with SonataAdminBundle

### Step 0: Install SonataBlockBundle

Review preview section

### Step 1: Install SonataAdminBundle

Review [SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/index.html) installation

### Step 1: Enable your block:

``` yaml
# app/config/config.yml
sonata_block:
    blocks:
        ekino.newrelic.block:
...
sonata_admin:
    ...
    dashboard:
        blocks:
            - {
                position: left,
                type: ekino.newrelic.block.simple,
                settings: {
                    reference: 3Y5rCib3JmH   # Url charts (https://... or 3Y5rCib3JmH)
                }
              }
```

More details for [configuration](http://sonata-project.org/bundles/admin/master/doc/reference/configuration.html) SonataAdminBundle

## Integration with Twig

``` twig
{{ sonata_block_render({ 'type': 'ekino.newrelic.block.simple' }, {
    'reference': '3Y5rCib3JmH'
}) }}
```
More details for [Twig extension](http://sonata-project.org/bundles/block/master/doc/reference/twig_helpers.html)
