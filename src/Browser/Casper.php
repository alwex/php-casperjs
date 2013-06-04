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

    /**
     * @param array $output
     * @return Casper
    */
    public function setOutput($output)
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
    public function clear()
    {
        $this->_script = '';
    }

    /**
     * open the specified url
     *
     * @param unknown $url
     */
    public function start($url)
    {
        $fragment =<<<FRAGMENT
var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    colorizerType: 'Dummy'
});

casper.start('$url', function() { });

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

        $this->setOutput($output);

        return $output;
    }

    public function getCurrentUrl()
    {
        $currentUrl = null;
        foreach ($this->getOutput() as $outputLine) {
            if (strpos($outputLine, $this->_TAG_CURRENT_URL) !== false) {
                $currentUrl = str_replace($this->_TAG_CURRENT_URL, '', $outputLine);
                break;
            }
        }

        return $currentUrl;
    }
}