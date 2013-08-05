<?php
/**
 * BanchaDispatcherTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha.Test.Case.Routing
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('AppController', 'Controller');
App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * TestsController class
 *
 * @package       Bancha.Test.Case.Routing
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class TestsController extends AppController {

	public function testaction1() {
		return array('text' => 'Hello World!');
	}

	public function testaction2() {
		return array('text' => 'foobar');
	}

}

/**
 * BanchaDispatcherTest
 *
 * @package       Bancha.Test.Case.Routing
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class BanchaDispatcherTest extends CakeTestCase {

/**
 * Tests the dispatch() method of BanchaDispatcher with the 'return'-option. Thus dispatch() doesn't send the response
 * to the browser but returns it instead. We are able to mock the BanchaRequest object, but we are not able to mock
 * the other objects used by the Dispatcher. Especially we need to provide an actual controller class. TestsController is
 * defined at the bottom of this file.
 *
 * This tests dispatches two actions and tests if the expected content is available in the combined response.
 *
 */
	public function testDispatchWithReturn() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'Test', // will be pluralized
				'method'	=> 'testaction1',
				'data'		=> array(),
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction2',
				'data'		=> array(),
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to net set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertTrue(isset($responses[0]->result), 'Expected $responses[0]->result to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Hello World!', $responses[0]->result->data->text);
		$this->assertEquals('foobar', $responses[1]->result->data->text);
	}

/**
 * Tests the dispatch() method of BanchaDispatcher without the 'return'-option. Thus dispatch() sends the response
 * directly to the browser. We need to capture the output to test it.
 *
 */
	public function testDispatchWithResponseSend() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction2',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to net set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$responses = json_decode(ob_get_clean());

		// verify
		$this->assertTrue(isset($responses[0]->result), 'Expected $responses[0]->result to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Hello World!', $responses[0]->result->data->text);
		$this->assertEquals('foobar', $responses[1]->result->data->text);
	}

/**
 * Bancha should not throw PHP Exceptions, because Sencha can't handle this,
 * instead it should send Ext.Direct exceptions
 *
 * @return void
 */
	public function testMissingController() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'SomeController',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to net set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// this should "throw" a Sencha exception
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertTrue(isset($responses[0]->type), 'Expected $responses[0]->type to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('MissingControllerException', $responses[0]->exceptionType);
		$this->assertEquals('Controller class SomeControllersController could not be found.', $responses[0]->message);
	}

}
