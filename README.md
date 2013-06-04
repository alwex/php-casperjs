php-casperjs
============

simple PHP wrapper for CasperJS

Usage :
```php
<?php
use Browser\Casper;

$casper = new Casper();
$casper->start('http://www.google.com');
$casper->fillForm(
        'form[action="/search"]',
        array(
                'q' => 'search'
        ),
        true);

$output = $casper->run();

var_dump($output);
        
```