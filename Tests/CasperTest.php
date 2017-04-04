<?php
use Browser\Casper;

class CasperTest extends PHPUnit_Framework_TestCase
{
    private static $casperBinPath = '/usr/local/bin/';

    public static function setUpBeforeClass()
    {
        if (!file_exists(self::$casperBinPath . 'casperjs')) {
            self::$casperBinPath = 'node_modules/casperjs/bin/';
        }
    }

    public function testCreateInstance()
    {
        $casper = new Casper(self::$casperBinPath);


        $this->assertInstanceOf('Browser\Casper', $casper);
    }

    public function testStart_onGoogleSearchPage()
    {
        $casper = new Casper(self::$casperBinPath);

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

    public function testStart_onGoogleSearchPageWithSlimerJS()
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => 'slimerjs']);
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

    public function testStart_onGoogleSearchPageWithIgnoreSSLErrorOption()
    {
        $casper = new Casper(self::$casperBinPath);
        $casper->setOptions(array(
            'ignore-ssl-errors' => 'yes'
        ));

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

        $casper = new Casper(self::$casperBinPath);

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
        $casper = new Casper(self::$casperBinPath);

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

        $casper = new Casper(self::$casperBinPath);

        $casper->start('http://www.google.com');
        $casper->wait(3000);
        $casper->run();

        $endSecond = time();

        $this->assertTrue($endSecond - $startSecond > 2);
    }

    public function testWaitForSelector()
    {
        $casper = new Casper(self::$casperBinPath);

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

        $casper = new Casper(self::$casperBinPath);

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

        $casper = new Casper(self::$casperBinPath);

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

    public function testSwitchToChildFrame()
    {
        $html = <<< HTML
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

        $casper = new Casper(self::$casperBinPath);

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

    public function testEvaluate()
    {
        $evaluateHtml = <<<TEXT
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>test evaluate</title>
    </head>
    <body>
        <a id="theLink" href='http://www.google.com' onclick='return confirm("are you sure")'>link to google</a>
    </body>
</html>
TEXT;
        $filename = '/tmp/test-evaluate.html';

        file_put_contents($filename, $evaluateHtml);

        $casper = new Casper(self::$casperBinPath);
        $casper->start($filename)
            ->click('#theLink')
            ->run();

        $this->assertContains('google', $casper->getCurrentUrl());

        $casper = new Casper(self::$casperBinPath);
        $casper->start($filename)
            ->evaluate('document.getElementById("theLink").href="http://www.yahoo.com";')
            ->click('#theLink')
            ->run();

        $this->assertContains('yahoo.com', $casper->getCurrentUrl());

        @unlink($filename);
    }

    public function testDoubleClick()
    {
        $evaluateHtml = <<<TEXT
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>test evaluate</title>
    </head>
    <body>
        <script type="text/javascript">
        function increase() {
            document.getElementById('theField').value++;
        }

        </script>
        <a id="theLink" href='#' onclick='javascript:increase()'>test</a>
        <input type="text" value="0" id="theField" />
    </body>
</html>
TEXT;
        $filename = '/tmp/test-click.html';
        file_put_contents($filename, $evaluateHtml);

        $casper = new Casper(self::$casperBinPath);
        $casper->start($filename)
            ->click("#theLink")
            ->run();

        @unlink($filename);
    }

    public function testInjectCustomScript()
    {
        $casper = new Casper(self::$casperBinPath);
        $casper->start('about:blank')
            ->addToScript(<<<FRAGMENT
casper.log('ABCDEFGH');
FRAGMENT
            )
            ->run();

        $found = false;
        foreach ($casper->getOutput() as $logLine) {
            if (preg_match('/ABCDEFGH/', $logLine)) {
                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    /**
     * @return array
     */
    public function getEngines()
    {
        return [
            ['phantomjs'],
            ['slimerjs'],
        ];
    }

    /**
     * @dataProvider getEngines
     * @param string $engine
     */
    public function testHeaders($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('http://www.google.com');
        $casper->run();

        $headers = $casper->getHeaders();
        $keys = array_column($headers, 'name');

        $this->assertContains('Date', $keys);
        $this->assertContains('Content-Type', $keys);
        $this->assertContains('Cache-Control', $keys);
    }

    /**
     * @dataProvider getEngines
     * @param string $engine
     */
    public function testGetStatus($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('https://www.w3.org/');
        $casper->run();

        $status = $casper->getStatus();

        $this->assertSame(200, $status);
    }

    /**
     * @dataProvider getEngines
     * @param string $engine
     */
    public function testGetStatusText($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('https://www.w3.org/');
        $casper->run();

        $statusText = $casper->getStatusText();

        $this->assertSame('OK', $statusText);
    }

    /**
     * @dataProvider getEngines
     * @param string $engine
     */
    public function testGetCookies($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('https://twitter.com');
        $casper->run();

        $cookies = $casper->getCookies();

        $firstCookie = call_user_func_array('array_merge', $cookies);

        $domains = array_unique(array_column($cookies, 'domain'));

        $this->assertArrayHasKey('domain', $firstCookie);
        $this->assertArrayHasKey('expires', $firstCookie);
        $this->assertArrayHasKey('expiry', $firstCookie);
        $this->assertArrayHasKey('httponly', $firstCookie);
        $this->assertArrayHasKey('name', $firstCookie);
        $this->assertArrayHasKey('path', $firstCookie);
        $this->assertArrayHasKey('secure', $firstCookie);
        $this->assertArrayHasKey('value', $firstCookie);

        $this->assertContains('.twitter.com', $domains);
    }
}
