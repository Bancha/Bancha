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

class BanchaRequestTest extends CakeTestCase 
{
    // test the getRequest function
    function testgetRequest() {

    	$banchaRequest = new BanchaRequest();
    	$request = $banchaRequest->getRequest();

    	for($i=0; $i<count($request); $i++) {
    		$this->assertEquals($request[$i]["action"], "create");
    	}
    }
}

?>