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
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/ArticlesController.php';

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
			'action'		=> 'ArticlesException',
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
		// $this->assertNotNull($responses[0]->data);
		
	}
	
	public function testExceptionDebugZero() {
		
		Configure::write('debug', 0);
		
		$rawPostData = json_encode(array(
			'action'		=> 'ArticlesException',
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
		
		$rawPostData = json_encode(array(
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'view',
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(
					'title'			=> 'Hello World',
					'body'			=> 'foobar',
					'published'		=> false,
					'user_id'		=> 1,
				),
			),
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'view',
				'tid'			=> 2,
				'type'			=> 'rpc',
				'data'			=> array(
					'title'			=> 'Hello World1',
					'body'			=> 'foobar1',
					'published'		=> false,
					'user_id'		=> 2,
				),
			),
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'view',
				'tid'			=> 3,
				'type'			=> 'rpc',
				'data'			=> array(
					'title'			=> 'Hello World2',
					'body'			=> 'foobar2',
					'published'		=> false,
					'user_id'		=> 1,
				),
			)
		));
		
		
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(3, count($responses), 'Three requests result in three responses.');
		
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('Invalid article [EXCEPTION]', $responses[0]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $GLOBALS['EXCEPTION_LINE'] . '.',
				$responses[0]->where, 'message');
		
		$this->assertEquals('exception', $responses[1]->type);
		$this->assertEquals('Invalid article [EXCEPTION]', $responses[1]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $GLOBALS['EXCEPTION_LINE'] . '.',
				$responses[1]->where, 'message');
				
		$this->assertEquals('exception', $responses[2]->type);
		$this->assertEquals('Invalid article [EXCEPTION]', $responses[2]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $GLOBALS['EXCEPTION_LINE'] . '.',
				$responses[2]->where, 'message');
	}
	// TODO: create 3 Controller (through 3 requests) and 1 of them throws an exception
}

/**
 * Articles Controller, uses view method to throw an exception
 *
 */
class ArticlesExceptionController extends ArticlesController {

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$GLOBALS['EXCEPTION_LINE'] = __LINE__; throw new Exception(__('Invalid article [EXCEPTION]'));
	}
}

