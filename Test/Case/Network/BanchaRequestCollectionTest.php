<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequestCollection', 'Bancha');
App::import('Lib','Bancha.Bancha.Network');

echo realpath(dirname(__FILE__) . '/../../../lib/Bancha') . "\n\n";


//TODO: Unnötig ??
set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Network/BanchaRequestCollection.php';
/**
 * BanchaRequestCollectionTest
 *
 * @package bancha.libs
 */
class BanchaRequestCollectionTest extends CakeTestCase
{
    // test the getRequest function
    function testgetRequest() {
    	
		$_POST = '{"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"rpc","tid":1}';
							  
		$collection = new BanchaRequestCollection();
    	$request = $collection->getRequests();
    	
    	//echo "responses:";
    	//print_r($request);
    	
		if (count($request) > 0) {
	    	for($i=0; $i<count($request); $i++) {
	    		$this->assertEquals($request[$i]["data"]["action"], "create");
	    	}
		}
    }
    
	function testgetRequests() {
			$_POST = '{"action":"create","method":"getRequests","data":[{"page":1,"start":0,"limit":25,"sort":[{"property":"name","direction":"ASC"}]}],"type":"json","tid":1}, 
					{"action":"update","method":"getRequests","data":[{"page":1,"start":0,"limit":10,"sort":[{"property":"name","direction":"ASC"}]}],"type":"json","tid":2}';
								  
			$collection = new BanchaRequestCollection();
	    	$request = $collection->getRequests();
	    	
	    	//echo "responses:";
	    	//print_r($request);
	    	
			if (count($request) > 0) {
				$this->assertEquals($request[0]["data"]["action"], "create");
				$this->assertEquals($request[1]["data"]["action"], "update");
			}
	    }
	}

?>