<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */
require_once 'BanchaRequest.php';
require_once 'PHPUnit.php';

class BanchaRequestTest extends PHPUnit_TestCase
{
    // contains the object handle of the string class
    var $abc;

    // constructor of the test suite
    function StringTest($name) {
       $this->PHPUnit_TestCase($name);
    }

    // called before the test functions will be executed
    // this function is defined in PHPUnit_TestCase and overwritten
    // here
    function setUp() {
        // create a new instance of String with the
        // string 'abc'
        $this->abc = new String("abc");
    }

    // called after the test functions are executed
    // this function is defined in PHPUnit_TestCase and overwritten
    // here
    function tearDown() {
        // delete your instance
        unset($this->abc);
    }

    // test the toString function
    function testgetRequest() {
    }
}

?>