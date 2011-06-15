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
	function testgetRequests() {
		$rawPostData = array(
			'action'	=> 'Test',
			'method'	=> 'create',
			'data'		=> null,
			'type'		=> 'rpc',
			'tid'		=> 1,
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals(1, count($requests));
		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Test');
		// method -> actio AND "create" -> "add"
		$this->assertEquals($requests[0]['action'], 'add');
	}

/**
 * Transforms multiple Ext JS requests into CakePHP requests. Also transforms the indexes of action/controller and
 * method/action.
 *
 */
	function testgetRequestsMultiple() {
		$rawPostData = array(
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
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals(2, count($requests));
		// action -> controller
		$this->assertEquals($requests[0]['controller'], 'Test');
		$this->assertEquals($requests[1]['controller'], 'Test');
		// method -> action AND "create" -> "add" / "update" -> "edit"
		$this->assertEquals($requests[0]['action'], 'add');
		$this->assertEquals($requests[1]['action'], 'edit');
	}

/**
 * Ext JS action names (create, update, destroy, read) needs to be transformed into CakePHP action names
 * (add, edit, delete, view, index).
 *
 * @dataProvider getRequestsActionConverterProvider
 */
	public function testGetRequestsActionConverter($extAction, $extData, $cakeAction)
	{
		$rawPostData = array(
			'action'	=> 'Test',
			'method'	=> $extAction,
			'data'		=> $extData,
			'type'		=> 'rpc',
			'tid'		=> 1,
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals($requests[0]['action'], $cakeAction);
	}
	
/**
 * When the action is update/edit, destroy/delete, read/view than the ID needs to be added to the 'pass' array.
 *
 */
	public function testGetRequestsPassId()
	{
		foreach (array('update', 'destroy', 'read') as $action)
		{
			$rawPostData = array(
				'action'	=> 'Test',
				'method'	=> $action,
				'data'		=> array('id' => 42),
				'type'		=> 'rpc',
				'tid'		=> 1,
			);

			$collection = new BanchaRequestCollection(json_encode($rawPostData));
			$requests = $collection->getRequests();

			$this->assertEquals($requests[0]['pass']['id'], 42);
		}
	}

/**
 * Ext JS uses page, offset, limit and sort in the data array for pagination. CakePHP needs a paging array with 
 * page, limit and order. The sort in Ext looks like [property: X, direction: Y], in Cake like [Controller.X => Y].
 *
 * @dataProvider getRequestsPaginationDataProvider
 */	
	public function testGetRequestsPagination($extData, $cakePaginate)
	{
		$rawPostData = array(
			'action'	=> 'Test',
			'method'	=> 'read',
			'data'		=> $extData,
			'type'		=> 'rpc',
			'tid'		=> 1,
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals($requests[0]['paging']['Test']['page'], $cakePaginate['page']);
		$this->assertEquals($requests[0]['paging']['Test']['limit'], $cakePaginate['limit']);
		$this->assertEquals($requests[0]['paging']['Test']['order'], $cakePaginate['order']);
	}
	
/**
 * Data provider for testGetRequestsActionConverter().
 *
 */
	public function getRequestsActionConverterProvider()
	{
		return array(
			array('create', array(), 'add'),
			array('update', array('id' => 1), 'edit'),
			array('destroy', array('id' => 1), 'delete'),
			array('read', array('id' => 1), 'view'),
			array('read', array(), 'index'),
		);
	}

/**
 * Data provider for testGetRequestsPagination().
 *
 */
	public function getRequestsPaginationDataProvider()
	{
		return array(
			// Default values
			array(
				array(),
				array(
					'page'		=> 1,
					'limit'		=> 25,
					'order'		=> array(),
				),
			),
			array(
				array(
					'page'		=> 2,
					'limit'		=> 10,
					'sort'		=> array(
						array(
							'property'		=> 'title',
							'direction'		=> 'ASC',
						),
					),
				),
				array(
					'page'		=> 2,
					'limit'		=> 10,
					'order'		=> array(
						'Test.title'	=> 'asc',
					),
				),
			),
			// page = start / limit
			array(
				array(
					'start'		=> 10,
					'limit'		=> 5,
				),
				array(
					'page'		=> 2,
					'limit'		=> 5,
					'order'		=> array(),
				),
			),
		);
	}

}
