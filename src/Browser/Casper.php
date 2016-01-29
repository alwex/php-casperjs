<?php
namespace Browser;

/**
 * CasperJS wrapper
 *
 * installation:
 * 1 - install phantomJS: http://phantomjs.org/download.html
 * 2 - install CasperJS: http://casperjs.org/installation.html
 *
 * @author aguidet
 *
 */
class Casper
{
    private $_TAG_CURRENT_URL = '[CURRENT_URL]';
    private $_TAG_CURRENT_TITLE = '[CURRENT_TITLE]';
    private $_TAG_CURRENT_PAGE_CONTENT = '[CURRENT_PAGE_CONTENT]';
    private $_TAG_CURRENT_HTML = '[CURRENT_HTML]';

    private $_debug = false;
    private $_script = '';
    private $_output = array();
    private $_requestedUrls = array();
    private $_currentUrl = '';
    private $_userAgent = 'casper';
    // default viewport values
    private $_viewPortWidth = 1024;
    private $_viewPortHeight = 768;
    private $_current_page_content = '';
    private $_current_html = '';
    private $_load_time = '';
    private $_temp_dir = '/tmp';
    private $_path2casper = '/usr/local/bin/'; //path to CasperJS

    public function __construct($_path2casper = null, $_temp_dir = null){
        if($_path2casper)
            $this->_path2casper = $_path2casper;
        if($_temp_dir)
            $this->_temp_dir = $_temp_dir;
    }

    /**
     * Set the UserAgent
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->_userAgent = $userAgent;
    }

    private $_options = array();

    /**
     * enable debug logging into syslog
     *
     * @param unknown $debug
     * @return Casper
     */
    public function setDebug($debug)
    {
        $this->_debug = $debug;

        return $this;
    }

    public function setViewPort($width, $height)
    {
        $this->_viewPortWidth = $width;
        $this->_viewPortHeight = $height;

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.viewport($width, $height);
});

FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }


    /**
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    /**
     * set specific options to casperJS
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * @param array $output
     * @return Casper
     */
    private function _setOutput($output)
    {
        $this->_output = $output;

        return $this;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * clear the current casper script
     */
    private function _clear()
    {
        $this->_script = '';
        $this->_output = array();
        $this->_requestedUrls = array();
        $this->_currentUrl = '';
    }

    /**
     * open the specified url
     *
     * @param unknown $url
     *
     * @return \Browser\Casper
     */
    public function start($url, $options = null)
    {
        $this->_clear();
        $options['headers']['Accept'] = 'text/html';
        $options = json_encode($options);

        $fragment = <<<FRAGMENT
var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    colorizerType: 'Dummy'
});

casper.userAgent('$this->_userAgent');
casper.start().then(function() {
    this.open('$url', $options);
});

FRAGMENT;

        $this->_script = $fragment;

        return $this;
    }

    /**
     * Open URL after the initial opening
     * @param $url
     * @return $this
     */
    public function thenOpen($url)
    {
        $fragment = <<<FRAGMENT
casper.thenOpen('$url');

FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * fill the form with the array of data
     * then submit it if submit is true
     *
     * @param unknown $selector
     * @param unknown $data
     * @param string $submit
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

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * Sends native keyboard events
     * to the element matching the provided selector:
     *
     * @param unknown $selector
     * @param unknown $string
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

        $this->_script .= $fragment;

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

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * wait until timeout
     *
     * @param number $timeout
     * @return \Browser\Casper
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

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     *
     * @return \Browser\Casper
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

        $this->_script .= $fragment;

        return $this;
    }

    /**
     *
     * @param unknown $selector
     *
     * @return \Browser\Casper
     */
    public function click($selector)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.click('$selector');
});

FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * take a screenshot of the page
     * area containing the selector
     *
     * @param string $selector
     * @param string $filename
     *
     * @return \Browser\Casper
     */
    public function captureSelector($selector, $filename)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.captureSelector('$filename', '$selector');
});

FRAGMENT;

        $this->_script .= $fragment;

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
     * @return \Browser\Casper
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

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * take a screenshot of the whole page
     * area defined by viewport width
     * and rendered height
     *
     * @param string $filename
     *
     * @return \Browser\Casper
     */
    public function capturePage($filename)
    {

        $fragment = <<<FRAGMENT
casper.on('load.finished', function() {
    this.capture('$filename', {
        top: 0,
        left: 0,
        width: $this->_viewPortWidth,
        height: this.evaluate(function() {
        return __utils__.getDocumentHeight();
        }),
    });
});
FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * switch to the child frame number $id
     *
     * @param unknown $id
     * @return \Browser\Casper
     */
    public function switchToChildFrame($id)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToChildFrame($id);
});

FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * get back to parent frame
     *
     * @return \Browser\Casper
     */
    public function switchToParentFrame()
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToParentFrame();
});

FRAGMENT;

        $this->_script .= $fragment;

        return $this;

    }


    public function evaluate($function)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    casper.evaluate(function() {
        $function
    });
});

FRAGMENT;

        $this->_script .= $fragment;

        return $this;
    }

    /**
     * run the casperJS script and return the stdOut
     * in using the output variable
     *
     * @return array
     */
    public function run()
    {
        $output = array();

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.echo('$this->_TAG_CURRENT_URL' + this.getCurrentUrl());
    this.echo('$this->_TAG_CURRENT_TITLE' + this.getTitle());
    this.echo('$this->_TAG_CURRENT_PAGE_CONTENT' + this.getPageContent().replace(new RegExp('\\r?\\n','g'), ''));
    this.echo('$this->_TAG_CURRENT_HTML' + this.getHTML().replace(new RegExp('\\r?\\n','g'), ''));
});

casper.run();

FRAGMENT;

        $this->_script .= $fragment;
        $filename = tempnam($this->_temp_dir, 'php-casperjs-');

        file_put_contents($filename, $this->_script);

        // options parsing
        $options = '';
        foreach ($this->_options as $option => $value) {
            $options .= ' --'.$option.'='.$value;
        }

        exec($this->_path2casper.'casperjs '.$filename.$options, $output);
        if(empty($output))
            throw new \Exception('Can not find CasperJS.');

        $this->_setOutput($output);
        $this->_processOutput();

        unlink($filename);

        return $output;
    }

    /**
     * process the output after navigation
     * and fill the differents attributes for
     * later usage
     */
    private function _processOutput()
    {
        foreach ($this->getOutput() as $outputLine) {
            if (strpos($outputLine, $this->_TAG_CURRENT_URL) !== false) {
                $this->_currentUrl = str_replace($this->_TAG_CURRENT_URL, '', $outputLine);
            }

            if (strpos($outputLine, "Navigation requested: url=") !== false) {

                $frag0 = explode('Navigation requested: url=', $outputLine);
                $frag1 = explode(', type=', $frag0[1]);
                $this->_requestedUrls[] = $frag1[0];
            }

            if ($this->isDebug()) {
                syslog(LOG_INFO, '[PHP-CASPERJS] '.$outputLine);
            }
            if (strpos($outputLine, $this->_TAG_CURRENT_PAGE_CONTENT) !== false) {
                $this->_current_page_content = str_replace($this->_TAG_CURRENT_PAGE_CONTENT, '', $outputLine);
            }

            if (strpos($outputLine, $this->_TAG_CURRENT_HTML) !== false) {
                $this->_current_html = str_replace($this->_TAG_CURRENT_HTML, '', $outputLine);
            }

            if (strpos($outputLine, " steps in ") !== false) {
                $frag = explode(' steps in ', $outputLine);
                $this->_load_time = $frag[1];
            }
        }
    }

    public function getCurrentUrl()
    {
        return $this->_currentUrl;
    }

    public function getRequestedUrls()
    {
        return $this->_requestedUrls;
    }

    public function getCurrentPageContent()
    {
        return $this->_current_page_content;
    }

    public function getHTML()
    {
        return $this->_current_html;
    }

    public function getLoadTime()
    {
        return $this->_load_time;
    }
}
