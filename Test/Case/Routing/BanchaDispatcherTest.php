<?php

/**
 * BanchaDispatcherTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * @package bancha.libs
 */
class BanchaDispatcherTest extends CakeTestCase {
	
/**
 * Tests the dispatch() method of BanchaDispatcher with the 'return'-option. Thus dispatch() doesn't send the response
 * to the browser but returns it instead. We are able to mock the BanchaRequest object, but we are not able to mock
 * the other objects used by the Dispatcher. Especially we need to provide an actual controller class. MyController is
 * defined at the bottom of this file.
 *
 * This tests dispatches two actions and tests if the expected content is available in the combined response.
 *
 */
	public function testDispatchWithReturn() {
		$rawPostData = array(
			array(
				'action'	=> 'My',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'My',
				'method'	=> 'testaction2',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch($collection, array('return' => true)));
		
		$this->assertEquals('Hello World!', $responses[0]->data->text);
		$this->assertEquals('foobar', $responses[1]->data->text);
	}
	
/**
 * Tests the dispatch() method of BanchaDispatcher without the 'return'-option. Thus dispatch() sends the response
 * directly to the browser. We need to capture the output to test it.
 *
 */
	public function testDispatchWithoutReturn()
	{
		$rawPostData = array(
			array(
				'action'	=> 'My',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'My',
				'method'	=> 'testaction2',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		
		$dispatcher = new BanchaDispatcher();
		ob_start(); // capture output, because we want to test without return
		$dispatcher->dispatch($collection);
		$responses = json_decode(ob_get_contents());
		ob_end_clean();
		
		$this->assertEquals('Hello World!', $responses[0]->data->text);
		$this->assertEquals('foobar', $responses[1]->data->text);
	}
	
}

/**
 * MyController class
 *
 * @package       bancha.tests.cases
 */
class MyController extends AppController {
	
	public function testaction1() {
		return array('text' => 'Hello World!');
	}
	
	public function testaction2() {
		return array('text' => 'foobar');
	}
	
}
