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
        $htmlIframe = <<< IFRAME
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>iframe 2</title>
    </head>
    <body>
        <form id="form">
            <input name="field1" type="text" />
            <input name="field2" type="text" />
            <input name="field3" type="text" />
            <input name="field4" type="text" />
        </form>
    </body>
IFRAME;

        $html = <<< HTML
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>iframe 1</title>
    </head>
    <body>
        <iframe name="myiframe" src="iframe2.html" style="width:900px; height:800px;"></iframe>
    </body>
</html>
HTML;

        $iframeFilename = '/tmp/iframe2.html';
        $filename = '/tmp/iframe1.html';

        file_put_contents($filename, $html);
        file_put_contents($iframeFilename, $htmlIframe);

        $casper = new Casper(self::$casperBinPath);

        $casper->start('file:///tmp/iframe1.html')
            ->switchToChildFrame(0)
            ->fillForm('#form', array(
                'field1' => 'testing',
                'field2' => 'Jean Valjean',
                'field3' => '2017',
                'field4' => '123',
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
            if (preg_match('/Set "field1" field value to testing/', $logLine)) {
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
     * @throws Exception
     */
    public function testGetStatus($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('http://guidet.alexandre.free.fr');
        $casper->run();

        $status = $casper->getStatus();

        $this->assertSame(200, $status);
    }

    /**
     * @dataProvider getEngines
     * @param string $engine
     * @throws Exception
     */
    public function testGetStatusText($engine)
    {
        $casper = new Casper(self::$casperBinPath);

        $casper->setOptions(['engine' => $engine]);

        $casper->start('http://guidet.alexandre.free.fr');
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
