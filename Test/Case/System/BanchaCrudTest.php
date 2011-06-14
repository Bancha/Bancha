<?php
/**
 * BanchaCrudTest file.
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
 * BanchaCrudTest
 *
 * @package bancha.libs
 */
class BanchaCrudTest extends CakeTestCase {
	
	public function testAdd()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'create',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals(42, $responses[0]->data->id);
		$this->assertEquals('Hello World', $responses[0]->data->title);
		$this->assertEquals(false, $responses[0]->data->published);
		$this->assertEquals(1, $responses[0]->data->user_id);
	}
	
	public function testEdit()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'id'			=> 42,
				'published'		=> true,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data->id);
		$this->assertEquals('Hello World', $responses[0]->data->title);
		$this->assertEquals(true, $responses[0]->data->published);
		$this->assertEquals(1, $responses[0]->data->user_id);
	}
	
	public function testDelete()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> null
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(array(), $responses[0]->data);
	}
	
	public function testIndex()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> null
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data[0]->id);
		$this->assertEquals(43, $responses[0]->data[1]->id);
	}
	
	public function testView()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('id' => 42)
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data[0]->id);
		$this->assertEquals('Hello World', $responses[0]->data[0]->title);
		$this->assertEquals(true, $responses[0]->data[0]->published);
		$this->assertEquals(1, $responses[0]->data[0]->user_id);
	}
	
}

/**
 * Articles Controller
 *
 */
class ArticlesController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		return array(
			array(
				'id'		=> 42,
				'title'		=> 'Hello World',
				'published'	=> true,
				'user_id'	=> 1,
			),
			array(
				'id'		=> 43,
				'title'		=> 'Foo Bar',
				'published'	=> false,
				'user_id'	=> 1,
			),
		);
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		return array(
			array(
				'id'		=> $id,
				'title'		=> 'Hello World',
				'published'	=> true,
				'user_id'	=> 1,
			)
		);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		return array(
			'id'		=> 42,
			'title'		=> 'Hello World',
			'published'	=> false,
			'user_id'	=> 1,
		);
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		return array(
			'id'		=> $id,
			'title'		=> 'Hello World',
			'published'	=> true,
			'user_id'	=> 1,
		);
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		return array();
	}
}

