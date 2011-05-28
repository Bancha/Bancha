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

set_include_path(dirname(__FILE__) . '/../../lib' . PATH_SEPARATOR . get_include_path());

require_once 'C:/Users/Kung/Bancha/lib/Bancha/src/Network/BanchaRequest.php';

class BanchaRequestTest extends PHPUnit_Framework_TestCase
{
    

    // test the getRequest function
    function testgetRequest() {
    	
    	// creating fake ext direct req
    	// extreq: {"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"rpc","tid":1}
    	$_POST = '{"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"rpc","tid":1}';
        // create a new instance of Bancharequest
        $banchaRequest = new BanchaRequest();
    	$requests = $banchaRequest->getRequests();
    	
    	// This only tests one element Json
    	$this->assertEquals($requests["action"], "create");
    	
    	//TODO: SCHLEIFE
    	
    	// delete your instance
        
    }
}

?>