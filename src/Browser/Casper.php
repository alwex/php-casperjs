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
    private $_script = '';

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
casper.run(function() {
  this.test.renderResults(true, 0, this.cli.get('save') || false);
});

FRAGMENT;

        $this->_script .= $fragment;

        file_put_contents('/tmp/test-casperjs.js', $this->_script);
        exec('casperjs /tmp/test-casperjs.js', $output);

        return $output;
    }
}