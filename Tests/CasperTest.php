<?php
use Browser\Casper;

class CasperTest extends PHPUnit_Framework_TestCase
{
    public function testCreateInstance()
    {
        $casper = new Casper();
        $this->assertInstanceOf('Browser\Casper', $casper);
    }

    public function testStart_onGoogleSearchPage()
    {
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
    }
}