<?php
/**
 * BanchaDispatcherTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaDispatcherTest
 *
 * @package       Bancha
 * @category      Tests
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

		$collection = new BanchaRequestCollection($rawPostData);

		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch($collection, array('return' => true)));

		$this->assertTrue(isset($responses[0]->result), 'Expected $responses[0]->result to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Hello World!', $responses[0]->result->text);
		$this->assertEquals('foobar', $responses[1]->result->text);
	}

/**
 * Tests the dispatch() method of BanchaDispatcher without the 'return'-option. Thus dispatch() sends the response
 * directly to the browser. We need to capture the output to test it.
 *
 */
	public function testDispatchWithoutReturn() {
		$this->markTestSkipped("When executing the AllTests TestSuite the PHPUnit output buffers break this test.");
		
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

		$collection = new BanchaRequestCollection($rawPostData);

		$dispatcher = new BanchaDispatcher();
		
		// if there is already a buffer, save the current result
		$cakeTestBuffer = ob_get_clean();
		
		ob_start(); // capture output, because we want to test without return
		$dispatcher->dispatch($collection);
		$responses = json_decode(ob_get_clean());
		
		// ob_end_clean() does not restore the Content-Type, but we do not want to send the header in CLI mode.
		if (isset($_SERVER['HTTP_HOST'])) {
			header("Content-Type: text/html; charset=utf-8");
		}
		
		// if there was a buffer, refill it as before
		if($cakeTestBuffer!=FALSE) {
			ob_start();
			echo $cakeTestBuffer;
		}
		
		$this->assertEquals('Hello World!', $responses[0]->result->text);
		$this->assertEquals('foobar', $responses[1]->result->text);
	}

}

/**
 * TestsController class
 *
 * @package       Bancha
 * @category      TestFixtures
 */
class TestsController extends AppController {

	public function testaction1() {
		return array('text' => 'Hello World!');
	}

	public function testaction2() {
		return array('text' => 'foobar');
	}

}
