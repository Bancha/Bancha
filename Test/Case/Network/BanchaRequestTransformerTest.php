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
 */

App::uses('BanchaRequestTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTransformerTest
 *
 * @package bancha.libs
 */
class BanchaRequestTransformerTest extends CakeTestCase
{
	
/**
 * In the Ext JS request the name of the controller is stored as "action". We need to transform this.
 *
 */
	public function testGetController()
	{
		$transformer = new BanchaRequestTransformer(array(
			'action'		=> 'Test',
		));
		$this->assertNotNull($transformer->getController());
		$this->assertEquals('Test', $transformer->getController());
	}
	
/**
 * First the name of action is stored in the "method" property in the Ext JS request, second Ext JS use different names
 * for CRUD operations. We need to transform them.
 * Ext JS -> CakePHP
 * - create -> add
 * - update -> edit
 * - destroy -> delete
 * - read -> view (if an ID is provided in the Data array).
 * - read -> index (if no ID is provided in the Data array).
 *
 * @dataProvider getActionProvider
 */
	public function testGetAction($extAction, $extData, $cakeAction)
	{
		$transformer = new BanchaRequestTransformer(array(
			'method'		=> $extAction,
			'data'			=> $extData,
		));
		$this->assertNotNull($transformer->getAction());
		$this->assertEquals($cakeAction, $transformer->getAction());
	}
	
/**
 * If the Ext JS request contains an URL, we need to extract is from the request, because we need to pass it to the
 * Constructor of CakeRequest.
 *
 */
	public function testGetUrl()
	{
		$transformer = new BanchaRequestTransformer(array(
			'url'			=> '/test/action'
		));
		$this->assertNotNull($transformer->getUrl());
		$this->assertEquals('/test/action', $transformer->getUrl());
	}
	
/**
 * There are some params which are passed directly to the action method inside the controller. CakePHP stores them in
 * the 'pass' array inside the request. For the CRUD operations the only 'pass' parameter is 'id'. Therefore we extract
 * it from the normal data array and add it to the pass array.
 *
 */
	public function testGetPassParams()
	{
		$transformer = new BanchaRequestTransformer(array(
			'method'	=> 'update',
			'data'		=> array('id' => 42),
		));
		$this->assertEquals(array('id' => 42), $transformer->getPassParams());
	}
	
/**
 * Ext JS uses page, offset, limit and sort in the data array for pagination. CakePHP needs a paging array with 
 * page, limit and order. The sort in Ext looks like [property: X, direction: Y], in Cake like [Controller.X => Y].
 *
 * @dataProvider getPagingProvider
 */	
	public function testGetPaging($extData, $cakePaginate)
	{
		$data = array(
			'action'	=> 'Test',
			'data'		=> $extData,
		);

		$transformer = new BanchaRequestTransformer($data);
		$paging = $transformer->getPaging();

		$this->assertEquals($paging['page'], $cakePaginate['page']);
		$this->assertEquals($paging['limit'], $cakePaginate['limit']);
		$this->assertEquals($paging['order'], $cakePaginate['order']);
	}
	
/**
 * The data array (which represent POST parameters) in CakePHP only contains the actual data values but not the special
 * parameters. Thus we need to clean the data array from action, controller, paginate and pass parameters. We therefore
 * use the methods described and tested above.
 *
 */
	public function testGetCleanedDataArray()
	{
		$data = array(
			'action'	=> 'Test',
			'method'	=> 'read',
			'data'		=> array(
				'id'		=> 42,
				'page'		=> 2,
				'limit'		=> 10,
				'sort'		=> array(),
				'foo'		=> 'bar'
			),
			'type'		=> 'rpc',
			'tid'		=> 1,
		);

		$transformer = new BanchaRequestTransformer($data);
		$data = $transformer->getCleanedDataArray();
		$this->assertFalse(isset($data['action']));
		$this->assertFalse(isset($data['method']));
		$this->assertFalse(isset($data['id']));
		$this->assertFalse(isset($data['page']));
		$this->assertFalse(isset($data['limit']));
		$this->assertFalse(isset($data['sort']));
		$this->assertEquals('bar', $data['foo']);
	}
	
/**
 * Provides the action names from Ext JS and CakePHP for use in testGetAction().
 *
 */
	public function getActionProvider()
	{
		return array(
			array('create', array(), 'add'),
			array('update', array('id' => 42), 'edit'),
			array('destroy', array('id' => 42), 'delete'),
			array('read', array('id' => 42), 'view'),
			array('read', array(), 'index'),
		);
	}
	
/**
 * Data provider for testGetRequestsPagination().
 *
 */
	public function getPagingProvider()
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
