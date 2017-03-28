<?php
namespace Browser;

/**
 * CasperJS wrapper
 *
 * @author aguidet
 */
class Casper
{
    private $TAG_CURRENT_URL = '[CURRENT_URL]';
    private $TAG_CURRENT_TITLE = '[CURRENT_TITLE]';
    private $TAG_CURRENT_PAGE_CONTENT = '[CURRENT_PAGE_CONTENT]';
    private $TAG_CURRENT_HTML = '[CURRENT_HTML]';
    private $TAG_CURRENT_HEADERS = '[CURRENT_HEADERS]';
    private $TAG_CURRENT_STATUS = '[CURRENT_STATUS]';
    private $TAG_CURRENT_STATUS_TEXT = '[CURRENT_STATUS_TEXT]';
    private $TAG_CURRENT_COOKIES = '[CURRENT_COOKIES]';

    private $debug = false;
    private $options = array();
    private $script = '';
    private $output = array();
    private $requestedUrls = array();
    private $currentUrl = '';
    private $userAgent = 'casper';
    // default viewport values
    private $viewPortWidth = 1024;
    private $currentPageContent = '';
    private $currentHtml = '';
    private $loadTime = '';
    private $tempDir = '/tmp';
    private $path2casper = '/usr/local/bin/'; //path to CasperJS
    private $headers = [];
    private $status;
    private $statusText = '';
    private $cookies = [];

    public function __construct($path2casper = null, $tempDir = null)
    {
        if ($path2casper) {
            $this->path2casper = $path2casper;
        }
        if ($tempDir) {
            $this->tempDir = $tempDir;
        }
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath2Casper($path)
    {
        $this->path2casper = $path;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath2Casper()
    {
        return $this->path2casper;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $headersScript = "
casper.page.customHeaders = {
";
        if (!empty($headers)) {
            $headerLines = [];
            foreach ($headers as $header => $value) {
                // Current version of casperjs will not decode gzipped output
                if ($header == 'Accept-Encoding') {
                    continue;
                }
                $headerLine = "    '{$header}': '";
                $headerLine .= (is_array($value)) ? implode(',', $value) : $value;
                $headerLine .= "'";
                $headerLines[] = $headerLine;
            }
            $headersScript .= implode(",\n", $headerLines) . "\n";
        }
        $headersScript .= "};";
        $this->script .= $headersScript;

        return $this;
    }

    /**
     * Set the UserAgent
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * enable debug logging into syslog
     *
     * @param bool $debug
     *
     * @return Casper
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    public function setViewPort($width, $height)
    {
        $this->viewPortWidth = $width;

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.viewport($width, $height);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }


    /**
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * set specific options to casperJS
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $output
     *
     * @return Casper
     */
    private function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * clear the current casper script
     */
    private function clear()
    {
        $this->script = '';
        $this->output = array();
        $this->requestedUrls = array();
        $this->currentUrl = '';
    }

    /**
     * open the specified url
     *
     * @param string $url
     *
     * @return \Browser\Casper
     */
    public function start($url)
    {
        $this->clear();

        $fragment = <<<FRAGMENT
var xpath = require('casper').selectXPath;
var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    colorizerType: 'Dummy'
});

casper.userAgent('$this->userAgent');
casper.start().then(function() {
    this.open('$url', {
        headers: {
            'Accept': 'text/html'
        }
    });
});

FRAGMENT;

        $this->script = $fragment;

        return $this;
    }

    /**
     * Open URL after the initial opening
     *
     * @param $url
     *
     * @return $this
     */
    public function thenOpen($url)
    {
        $fragment = <<<FRAGMENT
casper.thenOpen('$url');

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * fill the form with the array of data
     * then submit it if submit is true
     *
     * @param string $selector
     * @param array $data
     * @param string|bool $submit
     *
     * @return \Browser\Casper
     */
    public function fillForm($selector, $data = array(), $submit = false)
    {
        $jsonData = json_encode($data);
        $jsonSubmit = ($submit) ? 'true' : 'false';

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.fill('$selector', $jsonData, $jsonSubmit);
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function fillFormSelectors($selector, $data = array(), $submit = false)
    {
        $jsonData = json_encode($data);
        $jsonSubmit = ($submit) ? 'true' : 'false';

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.fillSelectors('$selector', $jsonData, $jsonSubmit);
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * Sends native keyboard events
     * to the element matching the provided selector:
     *
     * @param string $selector
     * @param string $string
     *
     * @return \Browser\Casper
     */
    public function sendKeys($selector, $string)
    {
        $jsonData = json_encode($string);

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.sendKeys('$selector', $jsonData);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     * @param integer $timeout
     *
     * @return \Browser\Casper
     */
    public function waitForText($text, $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.waitForText(
    '$text',
    function () {
        this.echo('found text "$text"');
    },
    function () {
        this.echo('timeout occured');
    },
    $timeout
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function wait($timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.wait(
    $timeout,
    function () {
        this.echo('timeout occured');
    }
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param $selector
     * @param int $timeout
     * @return $this
     */
    public function waitForSelector($selector, $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.waitForSelector(
    '$selector',
    function () {
        this.echo('found selector "$selector"');
    },
    function () {
        this.echo('timeout occured');
    },
    $timeout
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param $selector
     * @return $this
     */
    public function click($selector)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.click('$selector');
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * take a screenshot of the page
     * area containing the selector
     *
     * @param string $selector
     * @param string $filename
     *
     * @return $this
     */
    public function captureSelector($selector, $filename)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.captureSelector('$filename', '$selector');
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }


    /**
     * take a screenshot of the page
     * area defined by
     * array(top left width height)
     *
     * @param array $area
     * @param string $filename
     *
     * @return $this
     */
    public function capture(array $area, $filename)
    {
        $top = $area['top'];
        $left = $area['left'];
        $width = $area['width'];
        $height = $area['height'];

        $fragment = <<<FRAGMENT
casper.then(function() {
    this.capture('$filename', {
        top: $top,
        left: $left,
        width: $width,
        height: $height
    });
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * take a screenshot of the whole page
     * area defined by viewport width
     * and rendered height
     *
     * @param string $filename
     *
     * @return $this
     */
    public function capturePage($filename)
    {

        $fragment = <<<FRAGMENT
casper.on('load.finished', function() {
    this.capture('$filename', {
        top: 0,
        left: 0,
        width: $this->viewPortWidth,
        height: this.evaluate(function() {
        return __utils__.getDocumentHeight();
        }),
    });
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * switch to the child frame number $id
     *
     * @param string $id
     *
     * @return $this
     */
    public function switchToChildFrame($id)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToChildFrame($id);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * get back to parent frame
     *
     * @return $this
     */
    public function switchToParentFrame()
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToParentFrame();
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param $function
     * @return $this
     */
    public function evaluate($function)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    casper.evaluate(function() {
        $function
    });
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param $js
     * @return $this
     */
    public function addToScript($js)
    {
        $fragment = <<<FRAGMENT
$js
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * run the casperJS script and return the stdOut
     * in using the output variable
     *
     * @return array
     * @throws \Exception
     */
    public function run()
    {
        $output = array();

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.echo('$this->TAG_CURRENT_URL' + this.getCurrentUrl());
    this.echo('$this->TAG_CURRENT_TITLE' + this.getTitle());
    this.echo('$this->TAG_CURRENT_PAGE_CONTENT' + this.getPageContent().replace(new RegExp('\\r?\\n','g'), ''));
    this.echo('$this->TAG_CURRENT_HTML' + this.getHTML().replace(new RegExp('\\r?\\n','g'), ''));
    this.echo('$this->TAG_CURRENT_HEADERS' + JSON.stringify(this.currentResponse.headers));
    this.echo('$this->TAG_CURRENT_STATUS' + this.currentResponse.status);
    this.echo('$this->TAG_CURRENT_STATUS_TEXT' + this.currentResponse.statusText);
    this.echo('$this->TAG_CURRENT_COOKIES' + JSON.stringify(phantom.cookies));
});

casper.run();

FRAGMENT;

        $this->script .= $fragment;
        $filename = tempnam($this->tempDir, 'php-casperjs-');

        file_put_contents($filename, $this->script);

        // options parsing
        $options = '';
        foreach ($this->options as $option => $value) {
            $options .= ' --' . $option . '=' . $value;
        }

        exec($this->path2casper . 'casperjs ' . $filename . $options, $output);
        if (empty($output)) {
            throw new \Exception('Can not find CasperJS.');
        }

        $this->setOutput($output);
        $this->processOutput();

        unlink($filename);

        return $output;
    }

    /**
     * process the output after navigation
     * and fill the differents attributes for
     * later usage
     */
    private function processOutput()
    {
        foreach ($this->getOutput() as $outputLine) {
            if (strpos($outputLine, $this->TAG_CURRENT_URL) !== false) {
                $this->currentUrl = str_replace(
                    $this->TAG_CURRENT_URL,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, "Navigation requested: url=") !== false) {
                $frag0 = explode('Navigation requested: url=', $outputLine);
                $frag1 = explode(', type=', $frag0[1]);
                $this->requestedUrls[] = $frag1[0];
            }

            if ($this->isDebug()) {
                syslog(LOG_INFO, '[PHP-CASPERJS] ' . $outputLine);
            }
            if (strpos(
                $outputLine,
                $this->TAG_CURRENT_PAGE_CONTENT
            ) !== false
            ) {
                $this->currentPageContent = str_replace(
                    $this->TAG_CURRENT_PAGE_CONTENT,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, $this->TAG_CURRENT_HTML) !== false) {
                $this->currentHtml = str_replace(
                    $this->TAG_CURRENT_HTML,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, " steps in ") !== false) {
                $frag = explode(' steps in ', $outputLine);
                $this->loadTime = $frag[1];
            }

            if (0 === strpos($outputLine, $this->TAG_CURRENT_HEADERS)) {
                $this->headers = json_decode(str_replace($this->TAG_CURRENT_HEADERS, '', $outputLine), true);
            }

            if (0 === strpos($outputLine, $this->TAG_CURRENT_STATUS)) {
                $this->status = (int) str_replace($this->TAG_CURRENT_STATUS, '', $outputLine);
            }

            if (0 === strpos($outputLine, $this->TAG_CURRENT_STATUS_TEXT)) {
                $this->statusText = trim(str_replace($this->TAG_CURRENT_STATUS_TEXT, '', $outputLine));
            }

            if (0 === strpos($outputLine, $this->TAG_CURRENT_COOKIES)) {
                $this->cookies = json_decode(str_replace($this->TAG_CURRENT_COOKIES, '', $outputLine), true);
            }
        }
    }

    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    public function getRequestedUrls()
    {
        return $this->requestedUrls;
    }

    public function getCurrentPageContent()
    {
        return $this->currentPageContent;
    }

    public function getHTML()
    {
        return $this->currentHtml;
    }

    public function getLoadTime()
    {
        return $this->loadTime;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }
}
