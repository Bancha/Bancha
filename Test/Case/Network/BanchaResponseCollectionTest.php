<?php
/**
 * BanchaCrudTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */
class BanchaResponseCollectionTest extends CakeTestCase {

/**
 * Tests the getResponses() method.
 *
 * The test response is a batch response that contains three responses. The first two are successful, while the third
 * response is an exception.
 *
 * @return void
 * @author Florian Eckerstorfer
 */
	function testGetResponses() {
		// Content of responses.
		$response1 = array(
			'body'	=> array('message' => 'Hello World'),
		);
		$response2 = array(
			'body'	=> array('message' => 'Hello Bancha'),
		);
		$response3 = new Exception('This is an exception'); $exception_line = __LINE__;

		// ResponseCollection needs additional information from the request.
		$request1 = new CakeRequest();
		$request1->addParams(array('controller' => 'foo', 'action' => 'bar'));
		$request2 = new CakeRequest();
		$request2->addParams(array('controller' => 'bar', 'action' => 'foo'));
		$request3 = new CakeRequest();
		$request3->addParams(array('controller' => 'foo', 'action' => 'error'));

		// The heart of the test: create BanchaResponseCollection, add responses and get combined response.
		$collection = new BanchaResponseCollection();
		$collection->addResponse(1, new CakeResponse($response1), $request1)
				   ->addResponse(2, new CakeResponse($response2), $request2)
				   ->addException(3, $response3, $request3);
		// getResponses() is an CakeResponse with JSON as body.
		$actualResponse = json_decode($collection->getResponses()->body());

		// Successfull response
		$this->assertEquals('rpc', $actualResponse[0]->type);
		$this->assertEquals(1, $actualResponse[0]->tid);
		$this->assertEquals('foo', $actualResponse[0]->action);
		$this->assertEquals('bar', $actualResponse[0]->method);
		$this->assertEquals((object)array('message' => 'Hello World'), $actualResponse[0]->result);

		// Successfull response
		$this->assertEquals('rpc', $actualResponse[1]->type);
		$this->assertEquals(2, $actualResponse[1]->tid);
		$this->assertEquals('bar', $actualResponse[1]->action);
		$this->assertEquals('foo', $actualResponse[1]->method);
		$this->assertEquals((object)array('message' => 'Hello Bancha'), $actualResponse[1]->result);

		// Exception response
		$this->assertEquals('exception', $actualResponse[2]->type);
		$this->assertEquals('This is an exception', $actualResponse[2]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $exception_line . '.', $actualResponse[2]->where);
	}

/**
 * Tests the getResponses() method in combination with the 'extUpload' request parameter. If this parameter is true,
 * the response should not be JSON encoded but rather a valid HTML structure which contains the result inside a
 * <textarea>-element.
 *
 */
	public function testGetResponses_extUpload() {
		
		$response1 = array(
			'body'	=> array('message' => 'Hello World'),
		);
		$request = new CakeRequest();
		$request->addParams(array('controller' => 'foo', 'action' => 'bar', 'extUpload' => true));
		
		$collection = new BanchaResponseCollection();
		$collection->addResponse(2, new CakeResponse($response1), $request);

		$expected = '<html><body><textarea>[{"type":"rpc","tid":2,"action":"foo","method":"bar",'.
					'"result":'.json_encode($response1['body']).',"extUpload":true}]</textarea></body></html>';
		
		$this->assertEquals($expected, $collection->getResponses()->body());
	}

}
