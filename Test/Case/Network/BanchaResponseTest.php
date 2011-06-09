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


// TODO: kill, because not necessary?

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
		$banchaResponse->addResponse(new CakeResponse(array(
			'body'		=> 'test',
			'status'	=> 201,
			'type'		=> 'c',
			'charset'	=> 'UTF-8',
		)));
		$responses = $banchaResponse->getResponses;

		$this->assertEquals($response[0]['success'], false);
		$this->assertEquals($response[1]['data'], "test");
    }
	
	function testaddResponseSuccess() {
		$banchaResponse = new BanchaResponse();
		$banchaResponse->addResponse(new CakeResponse(array(
			'body'		=> 'test1',
			'status'	=> 200,
			'type'		=> 'c',
			'charset'	=> 'UTF-8',
		)));
		$responses = $banchaResponse->getResponses;
	
		$this->assertEquals($responses[0]['success'], true);
		$this->assertEquals($responses[0]['data'], "test1");
	}

	function testSend() {
		$response1 = array(
			'body'		=> 'test1',
			'status'	=> 200,
			'charset'	=> 'UTF-8',
		);
		$response2 = array(
			'body'		=> 'test2',
			'status'	=> 200,
			'type'		=> 'c',
			'charset'	=> 'UTF-8',
		);
		
		$banchaResponse = new BanchaResponse();
		$banchaResponse->addResponse(new CakeResponse($response1))
					   ->addResponse(new CakeResponse($response2));
		
		$actualResponse = $banchaResponse->send();
		$this->assertEquals(json_encode(array($response1, $response2)), $actualResponse);
		$this->assertEquals($response1['body'], $actualResponse[0]['body']);
		$this->assertEquals($response2['body'], $actualResponse[1]['body']);
	}
}

?>
