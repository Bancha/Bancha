<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */

class BanchaResponseCollectionTest extends CakeTestCase
{

	// function testaddResponseNoSuccess() {
	// 	$banchaResponse = new BanchaResponse();
	// 	$banchaResponse->addResponse(new CakeResponse(array(
	// 		'body'		=> 'test',
	// 		'status'	=> 201,
	// 		'type'		=> 'c',
	// 		'charset'	=> 'UTF-8',
	// 	)));
	// 	$responses = $banchaResponse->getResponses;
	// 
	// 	$this->assertEquals($response[0]['success'], false);
	// 	$this->assertEquals($response[1]['data'], "test");
	//     }
	// 
	// function testaddResponseSuccess() {
	// 	$banchaResponse = new BanchaResponse();
	// 	$banchaResponse->addResponse(new CakeResponse(array(
	// 		'body'		=> 'test1',
	// 		'status'	=> 200,
	// 		'type'		=> 'c',
	// 		'charset'	=> 'UTF-8',
	// 	)));
	// 	$responses = $banchaResponse->getResponses();
	// 
	// 	$this->assertEquals($responses[0]['success'], true);
	// 	$this->assertEquals($responses[0]['data'], "test1");
	// }

	function testGetResponses() {
		$response1 = array(
			'success'	=> true,
			'message'	=> 200,
			'body'		=> 'test1',
		);
		$response2 = array(
			'success'	=> true,
			'message'	=> 200,
			'body'		=> 'test1',
		);
		
		$expectedResponse1 = array(
			'success'	=> true,
			'message'	=> 200,
			'data'		=> 'test1',
		);
		$expectedResponse2 = array(
			'success'	=> true,
			'message'	=> 200,
			'data'		=> 'test1',
		);
		
		$transformer = new BanchaResponseCollection();
		$transformer->addResponse(new CakeResponse($response1))
					->addResponse(new CakeResponse($response2));
		
		$actualResponse = $transformer->getResponses()->body();
		// var_dump(json_decode($actualResponse->body()));
		$this->assertEquals(json_encode(array($expectedResponse1, $expectedResponse2)), $actualResponse);
		
		$actualResponse = json_decode($actualResponse);
		$this->assertEquals($expectedResponse1['data'], $actualResponse[0]->data);
		$this->assertEquals($expectedResponse2['data'], $actualResponse[1]->data);
	}
}

?>
