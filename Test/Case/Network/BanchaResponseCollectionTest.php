<?php
/**
 * BanchaResponseCollectionTest file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Network
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTest
 *
 * @package       Bancha.Test.Case.Network
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
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
	public function testGetResponses() {
		// Content of responses.
		$response1 = array(
			'body'	=> array(
				'success' => true,
				'message' => 'Hello World'),
		);
		$response2 = array(
			'body'	=> array(
				'success' => true,
				'message' => 'Hello Bancha'),
		);
		$response3 = array(
			'body'	=> array(
				'success' => true,
				'message' => 'Hello Plugin-World'),
		);
		$response4 = new Exception('This is an exception');
		$exceptionLine = __LINE__ - 1; // we care about the line above

		// ResponseCollection needs additional information from the request.
		$request1 = new CakeRequest();
		$request1->addParams(array('controller' => 'Foo', 'action' => 'bar'));
		$request2 = new CakeRequest();
		$request2->addParams(array('controller' => 'Bar', 'action' => 'foo'));
		$request3 = new CakeRequest();
		$request3->addParams(array('controller' => 'Foo', 'action' => 'bar', 'plugin' => 'Plafoo'));
		$request4 = new CakeRequest();
		$request4->addParams(array('controller' => 'Foo', 'action' => 'error'));

		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// The heart of the test: create BanchaResponseCollection, add responses and get combined response.
		$collection = new BanchaResponseCollection($response);
		$collection->addResponse(1, new CakeResponse($response1), $request1)
					->addResponse(2, new CakeResponse($response2), $request2)
					->addResponse(3, new CakeResponse($response3), $request3)
					->addException(4, $response4, $request4);
		// getResponses() is a CakeResponse with JSON as body.
		$actualResponse = json_decode($collection->getResponses()->body());

		// Successfull response
		$this->assertEquals('rpc', $actualResponse[0]->type);
		$this->assertEquals(1, $actualResponse[0]->tid);
		$this->assertEquals('Foo', $actualResponse[0]->action);
		$this->assertEquals('bar', $actualResponse[0]->method);
		$this->assertEquals((object)array('success' => true, 'message' => 'Hello World'), $actualResponse[0]->result);

		// Successfull response
		$this->assertEquals('rpc', $actualResponse[1]->type);
		$this->assertEquals(2, $actualResponse[1]->tid);
		$this->assertEquals('Bar', $actualResponse[1]->action);
		$this->assertEquals('foo', $actualResponse[1]->method);
		$this->assertEquals((object)array('success' => true, 'message' => 'Hello Bancha'), $actualResponse[1]->result);

		// Successfull response from plugin
		$this->assertEquals('rpc', $actualResponse[2]->type);
		$this->assertEquals(3, $actualResponse[2]->tid);
		$this->assertEquals('Plafoo.Foo', $actualResponse[2]->action);
		$this->assertEquals('bar', $actualResponse[2]->method);
		$this->assertEquals((object)array('success' => true, 'message' => 'Hello Plugin-World'), $actualResponse[2]->result);

		// Exception response
		$this->assertEquals('exception', $actualResponse[3]->type);
		$this->assertEquals('This is an exception', $actualResponse[3]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $exceptionLine . '.', $actualResponse[3]->where);
	}

/**
 * Tests the getResponses() method in combination with the 'extUpload' request parameter. If this parameter is true,
 * the response should not be JSON encoded but rather a valid HTML structure which contains the result inside a
 * <textarea>-element.
 *
 * @return void
 */
	public function testGetResponsesExtUpload() {
		$response1 = array(
			'body'	=> array(
				'success' => true,
				'message' => 'Hello World'),
		);
		$request = new CakeRequest();
		$request->addParams(array('controller' => 'foo', 'action' => 'bar', 'extUpload' => true));

		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$collection = new BanchaResponseCollection($response);
		$collection->addResponse(2, new CakeResponse($response1), $request);

		$expected = '<html><body><textarea>[{"type":"rpc","tid":2,"action":"foo","method":"bar",' .
					'"result":' . json_encode($response1['body']) . ',"extUpload":true}]</textarea></body></html>';

		$this->assertEquals($expected, $collection->getResponses()->body());
	}

}
