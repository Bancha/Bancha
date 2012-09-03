<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestCollectionTest
 *
 * @package       Bancha
 * @category      tests
 */
class BanchaRequestCollectionTest extends CakeTestCase {

/**
 * Transforms one Ext JS request into a CakePHP request. Transforms the indexes from Ext JS syntax (action + method)
 * into CakePHP syntax (controller + action).
 *
 */
	function testGetRequests() {
		$client_id = uniqid();

		// We need to provide a request which looks like an actual Ext JS request in JSON syntax.
		// It is notated as a PHP array and transformed into JSON because it is easier to read that way.
		$rawPostData = json_encode(array(
			'action'	=> 'Test',
			'method'	=> 'create',
			'data'		=> array(array('data'=>array(
				'__bcid'		=> $client_id,
			))),
			'type'		=> 'rpc',
			'tid'		=> 1,
		));

		// Create a new request collection and parse the requests by calling getRequests().
		$collection = new BanchaRequestCollection($rawPostData);
		$requests = $collection->getRequests();

		// This should generate 1 CakeRequest object packed in an array.
		$this->assertEquals(1, count($requests));
		$this->assertThat($requests[0], $this->isInstanceOf('CakeRequest'));

		// All requests should be POST requests
		$this->assertTrue($requests[0]->is('post'));

		// action -> controller
		// controller should be pluralized
		$this->assertEquals($requests[0]['controller'], 'Tests');
		// method -> action AND "create" -> "add"
		$this->assertEquals($requests[0]['action'], 'add');

		// Cake has some special params like paginate, pass and named. Assure that these are there.
		$this->assertTrue(isset($requests[0]['pass']));
		$this->assertTrue(isset($requests[0]['named']));

		// TID is set?
		$this->assertEquals(1, $requests[0]['tid']);

		// consistency id is recognized?
		$this->assertEquals($client_id, $requests[0]['client_id']);
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

		// All requests should be POST requests
		$this->assertTrue($requests[0]->is('post'));
		$this->assertTrue($requests[1]->is('post'));

		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Tests');
		$this->assertEquals($requests[1]['controller'], 'Tests');
		// method -> action AND "create" -> "add" / "update" -> "edit"
		$this->assertEquals($requests[0]['action'], 'add');
		$this->assertEquals($requests[1]['action'], 'edit');
	}

/**
 * Transforms one Ext JS form request into a CakePHP request. Transforms the indexes from Ext JS form syntax (action +
 * method) into CakePHP syntax (controller + action).
 *
 */
	function testGetRequestsForm() {
		$postData = array(
			'extAction'	=> 'Test',
			'extMethod'	=> 'submit',
			'extTID'	=> 1,
			'id'		=> 42,
			'title'		=> 'Hello World'
		);

		// Create a new request collection and parse the requests by calling getRequests().
		$collection = new BanchaRequestCollection('', $postData);
		$requests = $collection->getRequests();

		// This should generate 1 CakeRequest object packed in an array.
		$this->assertEquals(1, count($requests));
		$this->assertThat($requests[0], $this->isInstanceOf('CakeRequest'));

		// All requests should be POST requests
		$this->assertTrue($requests[0]->is('post'));

		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Tests');
		// method -> action AND "submit" + id -> "edit"
		$this->assertEquals($requests[0]['action'], 'edit');

		// Cake has some special params like paginate, pass and named. Assure that these are there.
		$this->assertTrue(isset($requests[0]['pass']));
		$this->assertTrue(isset($requests[0]['named']));
		
		// ID needs to be added to the 'pass' array.
		$this->assertEquals(42, $requests[0]['pass']['id']);

		// Title needs to be added to the data array.
		$this->assertEquals('Hello World', $requests[0]->data('Test.title'));

		// TID is set?
		$this->assertEquals(1, $requests[0]['tid']);
	}

/**
 * Tests if the extUpload parameter is correctly passed through the CakeRequest.
 */
	public function testGetExtUploadForm() {
		$postData = array(
			'extAction'	=> 'Test',
			'extMethod'	=> 'submit',
			'extTID'	=> 1,
			'id'		=> 42,
			'title'		=> 'Hello World',
			'extUpload'	=> true,
		);

		// Create a new request collection and parse the requests by calling getRequests().
		$collection = new BanchaRequestCollection('', $postData);
		$requests = $collection->getRequests();

		$this->assertEquals(true, $requests[0]['extUpload']);
	}

}
