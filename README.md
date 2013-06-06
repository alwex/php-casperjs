php-casperjs
============

php-casperjs is a simple PHP wrapper for the fine library CasperJS designed to automate 
user testing against web pages.

It is easy to integrate into PHPUnit test case.

Installation
------------

Before using php-casperjs, you need to install both library:

1 - **PhantomJS** http://phantomjs.org/download.html
2 - **CasperJS** http://casperjs.org/installation.html

Usage
-----

```php
<?php

use Browser\Casper;

$casper = new Casper();

// navigate to google web page
$casper->start('http://www.google.com');

// fill the search form and submit it
$casper->fillForm(
        'form[action="/search"]',
        array(
                'q' => 'search'
        ),
        true);

// wait for text if needed
$casper->waitForText('Yahoo');

// or wait for selector
$casper->waitForSelector('.gbqfb');

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

// run the casper script
$casper->run();

// check the urls casper get throught
var_dump($casper->getRequestedUrls());

// need to debug? just check the casper output
var_dump($casper->getOutput());
        
```