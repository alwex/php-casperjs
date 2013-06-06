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

    public function testWaitForText()
    {
        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->fillForm(
                'form[action="/search"]',
                array(
                        'q' => 'search'
                ),
                true);
        $casper->waitForText('Yahoo');
        $casper->click('h3.r a');
        $casper->run();

        $this->assertNotEmpty($casper->getRequestedUrls());
        $this->assertContains('http://www.google.com/', $casper->getRequestedUrls());
    }

    public function testWaitForSelector()
    {
        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->fillForm(
                'form[action="/search"]',
                array(
                        'q' => 'search'
                ),
                true);
        $casper->waitForSelector('.gbqfb');
        $casper->click('h3.r a');
        $casper->run();

        $this->assertNotEmpty($casper->getRequestedUrls());
        $this->assertContains('http://www.google.com/', $casper->getRequestedUrls());
    }

    public function testCaptureSelector()
    {
        $filename = '/tmp/casperjs-test.png';

        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->captureSelector('#hplogo', $filename);
        $casper->run();

        $this->assertFileExists($filename);
        unlink($filename);
        $this->assertFileNotExists($filename);
    }

    public function testCapture()
    {
        $filename = '/tmp/casperjs-test.png';

        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->capture(
                array(
                    'top' => 0,
                    'left' => 0,
                    'width' => 800,
                    'height' => 600
                ),
                $filename
        );
        $casper->run();

        $this->assertFileExists($filename);
        unlink($filename);
        $this->assertFileNotExists($filename);
    }
}