Ekino NewRelic Bundle
=====================

[![Build Status](https://secure.travis-ci.org/ekino/EkinoNewRelicBundle.png?branch=master)](http://travis-ci.org/ekino/EkinoNewRelicBundle)

This bundle integrates the NewRelic PHP API into Symfony2. For more information about NewRelic, please visit http://newrelic.com.

The bundle can use either the route name or the controller name as the transaction name. For CLI commands the transaction name is the command name.

## Result

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
    // ...
    application_name: Awesome Application # (mandatory, default value in newrelic is PHP Application)
    api_key:                              # New Relic API
    logging: false                        # If true, logs all New Relic interactions to the Symfony log
    instrument: false                     # If true, uses enhanced New Relic RUM instrumentation (see below)
    log_exceptions: false                 # If true, sends exceptions to New Relic
    log_commands: true                    # If true, logs CLI commands to New Relic as Background jobs (>2.3 only)
    transaction_naming: route             # route, controller or service (see below)
    transaction_naming_service: ~         # Transaction naming service (see below)
```

## Enhanced RUM instrumentation

The bundle comes with an option for enhanced real user monitoring. Ordinarily the New Relic extension (unless disabled by configuration) automatically adds a tracking code for RUM instrumentation to all HTML responses. Using enhanced RUM instrumentation, the bundle allows you to selectivly disable instrumentation on certain requests.

This can be useful if, e.g. you're returing HTML verbatim for an HTML editor.

If enhanced RUM instrumentation is enabled, you can *disable* instrumentation for a given request by passing along a ```_instrument``` request parameter, and setting it to ```false```. This can be done e.g. through the routing configuration.

## Transaction naming strategies

The bundle comes with two built-in transaction naming strategies. ```route``` and ```controller```, naming the New Relic transaction after the route or controller respectively. However, the bundle supports custom transaction naming strategies through the ```service``` configuration option. If you have selected the ```service``` configuration option, you must pass the name of your own transaction naming service as the ```transaction_naming_service``` configuration option.

The transaction naming service class must implement the ```Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface``` interface. For more information on creating your own services, see the Symfony documentation on [Creating/Configuring Services in the Container](http://symfony.com/doc/current/book/service_container.html#creating-configuring-services-in-the-container).