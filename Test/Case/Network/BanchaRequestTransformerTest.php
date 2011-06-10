<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequest', 'Bancha');
App::import('Lib','Bancha.Bancha.Network');

echo realpath(dirname(__FILE__) . '/../../../lib/Bancha') . "\n\n";


//TODO: Unnötig ??
set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Network/BanchaRequestTransformer.php';
/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */

class BanchaRequestTransformerTest extends CakeTestCase
{
    // test the getRequest function
    function testgetRequest() {
    	
		$_POST = '{"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"rpc","tid":1}';
							  
		$banchaRequest = new BanchaRequestTransformer();
    	$request = $banchaRequest->getRequests();
    	
    	//echo "responses:";
    	//print_r($request);
    	
		if (count($request) > 0) {
	    	for($i=0; $i<count($request); $i++) {
	    		$this->assertEquals($request[$i]["data"]["action"], "create");
	    	}
		}
    }
    
	function testgetRequests() {
	    	// TODO: test with more requests
			$_POST = '{"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"rpc","tid":1}';
								  
			$banchaRequest = new BanchaRequestTransformer();
	    	$request = $banchaRequest->getRequests();
	    	
	    	//echo "responses:";
	    	//print_r($request);
	    	
			if (count($request) > 0) {
		    	for($i=0; $i<count($request); $i++) {
		    		$this->assertEquals($request[$i]["data"]["action"], "create");
		    	}
			}
	    }
	}

?>