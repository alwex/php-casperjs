php-casperjs
============

[![Travis branch](https://img.shields.io/travis/alwex/php-casperjs/stable.svg)]()
[![Packagist](https://img.shields.io/packagist/dt/phpcasperjs/phpcasperjs.svg?maxAge=2592000)]()
[![Version](http://img.shields.io/packagist/v/phpcasperjs/phpcasperjs.svg?style=flat)](https://packagist.org/packages/phpcasperjs/phpcasperjs)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/64289c40-f11c-49ef-b295-2e7ec784d64a/big.png)](https://insight.sensiolabs.com/projects/64289c40-f11c-49ef-b295-2e7ec784d64a)

php-casperjs is a simple PHP wrapper for the fine library CasperJS designed to automate
user testing against web pages.

It is easy to integrate into PHPUnit test case.

Making webcrawler has never been so easy!

Installation
------------

Before using php-casperjs, you need to install both library:

1 - **PhantomJS** http://phantomjs.org/download.html

2 - **CasperJS** http://docs.casperjs.org/en/latest/installation.html

```shell
npm install phantomjs
npm install casperjs
```

then

```shell
composer require phpcasperjs/phpcasperjs
```

Usage
-----

```php
<?php

use Browser\Casper;

$casper = new Casper();

// forward options to phantomJS
// for example to ignore ssl errors
$casper->setOptions([
    'ignore-ssl-errors' => 'yes'
]);

// navigate to google web page
$casper->start('http://www.google.com');

// fill the search form and submit it with input's name
$casper->fillForm(
        'form[action="/search"]',
        array(
                'q' => 'search'
        ),
        true);

// or with javascript selectors:
$casper->fillFormSelectors(
        'form.form-class',
        array(
                'input#email-id' => 'user-email',
                'input#password-id'   =>  'user-password'
        ),true);

// wait for 5 seconds (have a coffee)
$casper->wait(5000);

// wait for text if needed for 3 seconds
$casper->waitForText('Yahoo', 3000);

// or wait for selector
$casper->waitForSelector('.gbqfb', 3000);

// make a screenshot of the google logo
$casper->captureSelector('#hplogo', '/tmp/logo.png');

// or take a screenshot of a custom area
$casper->capture(
    array(
        'top' => 0,
        'left' => 0,
        'width' => 800,
        'height' => 600
    ),
    '/tmp/custom-capture.png'
);

// click the first result
$casper->click('h3.r a');

// switch to the first iframe
$casper->switchToChildFrame(0);

// make some stuff inside the iframe
$casper->fillForm('#myForm', array(
    'search' => 'my search',
));

// go back to parent
$casper->switchToParentFrame();

// run the casper script
$casper->run();

// check the urls casper get through
var_dump($casper->getRequestedUrls());

// need to debug? just check the casper output
var_dump($casper->getOutput());

```

If you want to see your crawler in action, set the engine to slimerjs

```php
$casper = new Casper();
$casper->setOptions(['engine' => 'slimerjs']);
```
