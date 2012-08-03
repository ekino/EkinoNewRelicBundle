Ekino NewRelic Bundle
=====================

[![Build Status](https://secure.travis-ci.org/ekino/EkinoNewRelicBundle.png?branch=master)](http://travis-ci.org/ekino/EkinoNewRelicBundle)

This bundle integrates the NewRelic PHP API into Symfony2. For more information about NewRelic, please visit http://newrelic.com.

For now the bundle uses the route name as the transaction name on NewRelic.

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
    application_name: Awesome Aplication # (mandatory, default value in newrelic is PHP Application)
    api_key:                             # New Relic API
```
