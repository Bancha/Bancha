<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       bancha.libs
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestCollectionTest
 *
 * @package bancha.libs
 */
class BanchaRequestCollectionTest extends CakeTestCase
{
	
/**
 * Transforms one Ext JS request into a CakePHP request. Transforms the indexes from Ext JS syntax (action + method)
 * into CakePHP syntax (controller + action).
 *
 */
	function testGetRequests() {
		// We need to provide a request which looks like an actual Ext JS request in JSON syntax.
		// It is notated as a PHP array and transformed into JSON because it is easier to read that way.
		$rawPostData = json_encode(array(
			'action'	=> 'Test',
			'method'	=> 'create',
			'data'		=> null,
			'type'		=> 'rpc',
			'tid'		=> 1,
		));

		// Create a new request collection and parse the requests by calling getRequests().
		$collection = new BanchaRequestCollection($rawPostData);
		$requests = $collection->getRequests();
		
		// This should generate 1 CakeRequest object packed in an array.
		$this->assertEquals(1, count($requests));
		$this->assertThat($requests[0], $this->isInstanceOf('CakeRequest'));
		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Test');
		// method -> actio AND "create" -> "add"
		$this->assertEquals($requests[0]['action'], 'add');
		
		// Cake has some special params like paginate, pass and named. Assure that these are there.
		$this->assertTrue(isset($requests[0]['pass']));
		$this->assertTrue(isset($requests[0]['paging']));
		$this->assertTrue(isset($requests[0]['named']));
	}

/**
 * Transforms multiple Ext JS requests into CakePHP requests. Also transforms the indexes of action/controller and
 * method/action.
 *
 */
	function testGetRequestsMultiple() {
		// Again, the Ext JS request is notated in PHP syntax and transformed into JSON because it is easier to read
		// this way.
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'Test',
				'method'	=> 'create',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'update',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			),
		));

		$collection = new BanchaRequestCollection($rawPostData);
		$requests = $collection->getRequests();
		
		// Two requests should result in an array that contains 2 CakeRequest objects.
		$this->assertEquals(2, count($requests));
		$this->assertThat($requests[0], $this->isInstanceOf('CakeRequest'));
		$this->assertThat($requests[1], $this->isInstanceOf('CakeRequest'));
		
		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Test');
		$this->assertEquals($requests[1]['controller'], 'Test');
		// method -> action AND "create" -> "add" / "update" -> "edit"
		$this->assertEquals($requests[0]['action'], 'add');
		$this->assertEquals($requests[1]['action'], 'edit');
	}

}
