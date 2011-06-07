<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaResponse', 'Bancha');
App::uses('CakeResponse', 'Network');

App::import('Lib','Bancha.Bancha.Network');


// TODO: UNNÖTIG?

set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Network/BanchaResponse.php';

/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */

class BanchaResponseTest extends CakeTestCase
{
    function testaddResponseNoSuccess() {
    	$banchaResponse = new BanchaResponse();
    	/* CakeResponse: @param array $options list of parameters to setup the response. Possible values are:
 			*	- body: the rensonse text that should be sent to the client
 			*	- status: the HTTP status code to respond with
 			*	- type: a complete mime-type string or an extension mapepd in this class
 			*	- charset: the charset for the response body
 		*/
    	$banchaResponse->addResponse(new CakeResponse(
    								array('body' => "test", 'status' => "201", 'type' => 'c', 'charset' => "UTF-8")));
    	
    	$firstResponse = $banchaResponse->responses[0];
    	
    	$this->assertEquals($firstResponse['success'], false);
    	$this->assertEquals($firstResponse['body'], "test");
    }
	
    function testaddResponseSuccess() {
    	$banchaResponse = new BanchaResponse();
    	$banchaResponse->addResponse(new CakeResponse(
    								array('body' => "test1", 'status' => "200", 'type' => 'c', 'charset' => "UTF-8")));
    	
    	$firstResponse = $banchaResponse->responses[0];
    	
    	$this->assertEquals($firstResponse['success'], true);
    	$this->assertEquals($firstResponse['body'], "test1");
    }
    
	// TODO: test the send function
	function testSend() {
		
	}
}
?>