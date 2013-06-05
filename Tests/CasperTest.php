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
        $casper->click('h3.r a');
        $casper->run();

        $this->assertTrue(is_array($casper->getOutput()));
        $this->assertTrue(sizeof($casper->getOutput()) > 0);
        $this->assertNotNull($casper->getCurrentUrl());
    }

    public function testGetRequestedUrls()
    {
        $urls = array();

        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->fillForm(
                'form[action="/search"]',
                array(
                        'q' => 'search'
                ),
                true);
        $casper->click('h3.r a');
        $casper->run();

        $this->assertNotEmpty($casper->getRequestedUrls());
        $this->assertContains('http://www.google.com/', $casper->getRequestedUrls());
    }
}