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
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
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
		$_POST = json_encode(array(
			'url'		=> '/test/create',
			'action'	=> 'add',
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(new BanchaRequestCollection(), array('return' => true)));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testEdit()
	{
		$_POST = json_encode(array(
			'url'		=> '/test/update',
			'action'	=> 'edit',
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(new BanchaRequestCollection(), array('return' => true)));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testDelete()
	{
		$_POST = json_encode(array(
			'url'		=> '/test/destroy',
			'action'	=> 'delete',
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(new BanchaRequestCollection(), array('return' => true)));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
	public function testIndex()
	{
		$_POST = json_encode(array(
			'url'		=> '/test/read',
			'action'	=> 'index',
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(new BanchaRequestCollection(), array('return' => true)));
		
		$this->assertEquals(42, $responses[0]->data->id);
	}
	
}

class TestController extends AppController
{

	// TODO: rename to add()
	public function create()
	{
		return array('id' => 42);
	}

	// TODO: rename to update()
	public function update()
	{
		return array('id' => 42);
	}

	// TODO: rename to destroy
	public function destroy()
	{
		return array('id' => 42);
	}

	// TODO: rename to read
	public function read()
	{
		return array('id' => 42);
	}

}

