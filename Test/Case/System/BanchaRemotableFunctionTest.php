<?php
/**
 * BanchaRemotableFunctionTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');



class RemotableFunctionsController extends AppController {
	public function returnInputParameters($param1, $param2) {
		return array($param1, $param2);
	}
	public function returnInputRequestData($param1, $param2) {
		return $this->request->data;
	}
}
class RemotableFunctionWithRequestHandlersController extends RemotableFunctionsController {
	/**
	 * Add the RequestHandler here to test compability
	 */
	public $components = array('Session', 'RequestHandler', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));
}


/**
 * BanchaRemotableFunctionTest
 *
 * All these tests are using the full stack of CakePHP components, not only testing 
 * the functionallity of Bancha, but also that it is compatible to the current
 * CakePHP library (since Bancha is using some internal methods)
 * 
 * @package       Bancha
 * @category      Tests
 */
class BanchaRemotableFunctionTest extends CakeTestCase {
	public function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}

	public function testInputParametersAndReturnTransformation() {
		
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputParameters',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunction', $responses[0]->action);
		$this->assertEquals('returnInputParameters', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}

	public function testRequestDataAndReturnTransformation() {
		
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunction', $responses[0]->action);
		$this->assertEquals('returnInputRequestData', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}


}
