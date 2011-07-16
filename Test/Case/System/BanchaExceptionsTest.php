<?php
/**
 * BanchExceptionsTest file.
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
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

// Tests if the Exception was thrown, the correct controller was choosen
/**
 * BanchaCrudTest
 *
 * @package bancha.libs
 */
class BanchaExceptionsTest extends CakeTestCase {
	
/**
 * Tests the exceptions
 *
 */
	public function testExceptionDebugTwo() {
		
		Configure::write('debug', 2);
		
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'view',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertNotNull($responses[0]->result);
		// $this->assertNotNull($responses[0]->data);
		
	}
	
	public function testExceptionDebugZero() {
		
		Configure::write('debug', 0);
		
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'view',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(count($responses), 0);
	}
	
	// Test of serveral requests:
	// 3 exceptions
	public function testExceptions() {
		
		Configure::write('debug', 2);
		
		$rawPostData[0] = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'view',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		$rawPostData[1] = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'view',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World1',
				'body'			=> 'foobar1',
				'published'		=> false,
				'user_id'		=> 2,
			),
		));
		$rawPostData[2] = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'view',
			'tid'			=> 3,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World2',
				'body'			=> 'foobar2',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		
		$dispatcher = new BanchaDispatcher();
		// TODO: ask florian if this is the right solution
		for ($i = 0; $i < count($rawPostData); $i++) {
			$responses[$i] = json_decode($dispatcher->dispatch(
				new BanchaRequestCollection($rawPostData[$i]), array('return' => true)
			));
		}
		
		// print_r($responses);
		
		$this->assertEquals('exception', $responses[0][0]->type);
		$this->assertNotNull($responses[0][0]->result);
		
		/*
		$this->assertEquals('exception', $responses[0][1]->type);
		$this->assertNotNull($responses[0][1]->message);
		$this->assertNotNull($responses[0][1]->data);
		
		$this->assertEquals('exception', $responses[0][2]->type);
		$this->assertNotNull($responses[0][2]->message);
		$this->assertNotNull($responses[0][2]->data);
		*/
	}
	// TODO: create 3 Controller (through 3 requests) and 1 of them throws an exception
}

/**
 * Articles Controller, uses view method to throw an exception
 *
 */
class ArticlesExceptionController extends AppController {

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		throw new Exception(__('Invalid article'));
	}
}

