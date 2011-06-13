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

set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Routing/BanchaDispatcher.php';
require_once 'Routing/BanchaSingleDispatcher.php';
require_once 'Network/BanchaRequestCollection.php';
require_once 'Network/BanchaResponseCollection.php';

/**
 * @package bancha.libs
 */
class BanchaCrudTest extends CakeTestCase {
	
	public function testAdd()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'test',
			'method'		=> 'create',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('page' => 1, 'limit' => 10)
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testEdit()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'test',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> null
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testDelete()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'test',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> null
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testIndex()
	{
		$rawPostData = json_encode(array(
			'action'		=> 'test',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> null
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
}

class TestController extends AppController
{

	public function add()
	{
		return array('id' => 42);
	}

	public function edit()
	{
		return array('id' => 42);
	}

	public function delete()
	{
		return array('id' => 42);
	}

	public function index()
	{
		return array('id' => 42);
	}

}

