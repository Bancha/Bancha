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
	
	public function testGetController()
	{
		$transformer = new BanchaRequestTransformer(array(
			'action'		=> 'Test',
		));
		$this->assertNotNull($transformer->getController());
		$this->assertEquals('Test', $transformer->getController());
	}
	
/**
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
	
	public function testGetUrl()
	{
		$transformer = new BanchaRequestTransformer(array(
			'url'			=> '/test/action'
		));
		$this->assertNotNull($transformer->getUrl());
		$this->assertEquals('/test/action', $transformer->getUrl());
	}
	
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

		$this->assertEquals($paging['Test']['page'], $cakePaginate['page']);
		$this->assertEquals($paging['Test']['limit'], $cakePaginate['limit']);
		$this->assertEquals($paging['Test']['order'], $cakePaginate['order']);
	}
	
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
