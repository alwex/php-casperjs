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
        $casper->waitForText('Yahoo', 20000);
        $casper->click('h3.r a');
        $casper->run();

        $this->assertNotEmpty($casper->getRequestedUrls());
        $this->assertContains('http://www.google.com/', $casper->getRequestedUrls());
    }

    public function testWait()
    {
        $startSecond = time();

        $casper = new Casper();

        $casper->start('http://www.google.com');
        $casper->wait(3000);
        $casper->run();

        $endSecond = time();

        $this->assertTrue($endSecond - $startSecond > 2);
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
        $casper->waitForSelector('.gbqfb', 2000);
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

    public function testSwitchToChildFrame() {
        $html =<<< HTML
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>iframe 1</title>
    </head>
    <body>
        <iframe name="myiframe" src="https://psp.hipay.com/HiMediaPSP-war/Token.xhtml?p=MXPrDJr9zudQcQnVbDYKDie5a%2BwfqIzj7ngMUvWs%2Bwq4EB7HUVX2W0JF4Xf4n8YlqLGjIS8brZLdeQylx68L8GleJkXWWkzYMky9pPGZL35LQbAevTNU9PaZCkQuGPitWtel%2FUs65p3JSIKJC9mBGAx04ihP%2Ble3ZzJ949oSfh8xsJBofUw29Th1Z5%2BkYrkEVH04OR%2FKP3VloW%2FKNDYYMlw%2B4MTkzrIsqMPbENxuNS%2B5CJCpEMRDhTOh%2BFgCUjZrk62vgcdtbrXeKrmCNtDCfWMHI5xLo1qntxa%2FNcXUAMX8NZqFjZCj0PyROKVkHUc3QcVY%2FvVWJsbrqR8aW59PAGf%2FARyDbItUV1ktRP7aQexfn8xSO7GpldfPmEAopCM8tfMtS1%2B2bs0%3D" style="width:900px; height:800px;"></iframe>
    </body>
</html>
HTML;

        $filename = '/tmp/iframe1.html';

        file_put_contents($filename, $html);

        $year = date('Y');
        $year++;

        $casper = new Casper();

        $casper->start('file:///tmp/iframe1.html')
        ->switchToChildFrame(0)
        ->fillForm('#tokenizerForm', array(
                'tokenizerForm\:cardNumber' => 'testing',
                'tokenizerForm\:cardHolder' => 'Jean Valjean',
                'tokenizerForm\:cardExpiryYear' => $year,
                'tokenizerForm\:cardSecurityCode' => '123',
        ))
        ->switchToParentFrame()
        ->capture(
                array(
                        'top' => 0,
                        'left' => 0,
                        'width' => 800,
                        'height' => 600
                ),
                '/tmp/testage.png'
        )
        ->run();

        $found = false;
        foreach ($casper->getOutput() as $logLine) {
            if (preg_match('/Set "tokenizerForm:cardNumber" field value to testing/', $logLine)) {
                $found = true;
            }
        }

        $this->assertTrue($found);

        $this->assertFileExists($filename);
        unlink($filename);
        $this->assertFileNotExists($filename);

    }
}