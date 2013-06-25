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
    private $_TAG_CURRENT_PAGE_CONTENT ='[CURRENT_PAGE_CONTENT]';
    private $_TAG_CURRENT_HTML ='[CURRENT_HTML]';

    private $_script = '';
    private $_output = array();
    private $_requestedUrls = array();
    private $_currentUrl = '';

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
     */
    public function start($url)
    {
        $this->_clear();

        $fragment =<<<FRAGMENT
var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    colorizerType: 'Dummy'
});

casper.userAgent('Casper');
casper.start('$url', function() {
    headers: {
        'Accept': 'text/html'
    }
});

FRAGMENT;

        $this->_script = $fragment;
    }

    /**
     * fill the form with the array of data
     * then submit it if submit is true
     *
     * @param unknown $selector
     * @param unknown $data
     * @param string $submit
     */
    public function fillForm($selector, $data = array(), $submit = false)
    {
        $jsonData = json_encode($data);
        $jsonSubmit = ($submit) ? 'true' : 'false';

        $fragment =<<<FRAGMENT
casper.then(function () {
    this.fill('$selector', $jsonData, $jsonSubmit);
});

FRAGMENT;

        $this->_script .= $fragment;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     */
    public function waitForText($text)
    {
        $fragment =<<<FRAGMENT
casper.waitForText('$text', function () {
    this.echo('found text "$text"');
});
FRAGMENT;

        $this->_script .= $fragment;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     */
    public function waitForSelector($selector)
    {
        $fragment =<<<FRAGMENT
casper.waitForSelector('$selector', function () {
    this.echo('found selector "$selector"');
});
FRAGMENT;

        $this->_script .= $fragment;
    }

    public function click($selector)
    {
        $fragment =<<<FRAGMENT
casper.then(function() {
    this.click('$selector');
});

FRAGMENT;

        $this->_script .= $fragment;
    }

    /**
     * take a screenshot of the page
     * area containing the selector
     *
     * @param string $selector
     * @param string $filename
     */
    public function captureSelector($selector, $filename)
    {
        $fragment =<<<FRAGMENT
casper.then(function() {
    this.captureSelector('$filename', '$selector');
});
FRAGMENT;

        $this->_script .= $fragment;
    }


    /**
     * take a screenshot of the page
     * area defined by
     * array(top left width height)
     *
     * @param array $area
     * @param string $filename
     */
    public function capture(array $area, $filename)
    {
        $top    = $area['top'];
        $left   = $area['left'];
        $width  = $area['width'];
        $height = $area['height'];

        $fragment =<<<FRAGMENT
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

        $fragment =<<<FRAGMENT
casper.then(function () {
    this.echo('$this->_TAG_CURRENT_URL' + this.getCurrentUrl());
    this.echo('$this->_TAG_CURRENT_TITLE' + this.getTitle());
    this.echo('$this->_TAG_CURRENT_PAGE_CONTENT' + this.getPageContent());
    this.echo('$this->_TAG_CURRENT_HTML' + this.getHTML());
});

casper.run(function() {
  this.test.renderResults(true, 0, this.cli.get('save') || false);
});

FRAGMENT;

        $this->_script .= $fragment;

        file_put_contents('/tmp/test-casperjs.js', $this->_script);
        exec('casperjs /tmp/test-casperjs.js', $output);

        $this->_setOutput($output);
        $this->_processOutput();

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
}