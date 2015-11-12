<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
    require_once $file;
}

// Early versions of Twig did not use the Composer autoloader; load Twig's autoloader if necessary
if (!class_exists('Twig_Extension')) {
    require_once __DIR__.'/../vendor/twig/twig/lib/Twig/Autoloader.php';
    Twig_Autoloader::register();
}
