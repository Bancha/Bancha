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
 */

App::uses('BanchaRequestTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTransformerTest
 *
 * @package       Bancha
 * @category      tests
 */
class BanchaRequestTransformerTest extends CakeTestCase {
	
/**
 * Test input transformation of simple data
 */
	public function testTransformDataStructureToCake_SimpleData() {
		
		// setup
		$transformer = new BanchaRequestTransformer();
		
		
		// test 1 inside a data property
		$expected = array(
			'message' => 'value'
		);
		$input = array(
			'data' => $expected
		);
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
		
		
		// test 2 no data
		$input = array(
			'ignore' => 'me',
		);
		$expected = array();
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
	}
/**
 * Test input transformation for form data
 */
	public function testTransformDataStructureToCake_FormInput() {

		// setup
		$transformer = new BanchaRequestTransformer(array(
			'extAction' => 'Article', // currently a form action is recognized by the 'extAction' property
		));

		// in form the data is directly in the $data array
		$input = array(
			'id' => 3,
			'title' => 'foo',
			'body' => 'bar',
		);
		
		// result is a one-element cake record
		$expected = array(
			'Article' => array(
				'id' => 3,
				'title' => 'foo',
				'body' => 'bar',
			)
		);
		
		// test
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
	}
	
/**
 * Test input transformation of one record
 */
	public function testTransformDataStructureToCake_OneRecord() {
		
		$input = array('data' => array(
			array(
				'data' => array(
					'id' => 1,
					'name' => 'foo',
				),
			)
		));
		
		$expected = array(
			'Article' => array(
				'id' => 1,
				'name' => 'foo',
			),
		);
		
		$transformer = new BanchaRequestTransformer();
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
	}
	
/**
 * When is is a create action, delete the ext-generated id, so that cake recognizes that is a new record
 */
	public function testTransformDataStructureToCake_OneRecord_CreateAction() {
		
		$input = array('data' => array(
			array(
				'data' => array(
					'id' => 1,
					'name' => 'foo',
				),
			)
		));
		
		$expected = array(
			'Article' => array(
				// id is issing
				'name' => 'foo',
			),
		);
		
		$transformer = new BanchaRequestTransformer(array(
			'method' => 'create'
		));
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
	}

	
/**
 * Test input transformation of multiple records
 */
	public function testTransformDataStructureToCake_MultipleRecords() {
		// currently this is only supported when following config is true
		$currentConfig = Configure::read('Bancha.allowMultiRecordRequests');
		Configure::write('Bancha.allowMultiRecordRequests',true);
		
		
		// test
		$input = array('data' => array(
			array('data' => array(
				array( // first
					'id' => 1,
					'name' => 'foo',
				),
				array( // second
					'id' => 2,
					'name' => 'bar',
				),
			)
		)));
		
		$expected = array(
			'0' => array(
				'Article' => array(
					'id' => 1,
					'name' => 'foo',
				),
			),
			'1' => array(
				'Article' => array(
					'id' => 2,
					'name' => 'bar',
				),
			),
		);
		$transformer = new BanchaRequestTransformer();
		$this->assertEquals($expected, $transformer->transformDataStructureToCake('Article',$input));
		
		
		// tear down
		Configure::write('Bancha.allowMultiRecordRequests',$currentConfig);
	}
	
/**
 * In the Ext JS request the name of the controller is stored as "action". We need to transform this.
 *
 */
	public function testGetController() {
		$transformer = new BanchaRequestTransformer(array(
			'action'		=> 'Test',
		));
		$this->assertNotNull($transformer->getController());
		$this->assertEquals('Tests', $transformer->getController());
	}

/**
 * This tests is the same as {@see testGetController()} but for form requests.
 *
 */
	public function testGetControllerForm() {
		$transformer = new BanchaRequestTransformer(array(
			'extAction'		=> 'Test',
		));
		$this->assertNotNull($transformer->getController());
		$this->assertEquals('Tests', $transformer->getController());
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
	public function testGetAction($extAction, $extData, $cakeAction) {
		$transformer = new BanchaRequestTransformer(array(
			'method'		=> $extAction,
			'data'			=> $extData,
		));
		$this->assertNotNull($transformer->getAction());
		$this->assertEquals($cakeAction, $transformer->getAction());
	}

/**
 * Same as {@see testGetAction()} but for form requests.
 *
 * @dataProvider getActionFormProvider
 */
	public function testGetActionForm($extAction, $extData, $cakeAction) {
		$transformer = new BanchaRequestTransformer(array_merge(
			array('extMethod'		=> $extAction),
			$extData
		));
		$this->assertNotNull($transformer->getAction());
		$this->assertEquals($cakeAction, $transformer->getAction());
	}

/**
 * Tests if the extUpload parameter is correctly extracted from the request.
 *
 */
	public function testGetExtUpload() {
		$transformer = new BanchaRequestTransformer(array(
			'extUpload'		=> true,
		));
		$this->assertNotNull($transformer->getExtUpload());
		$this->assertEquals(true, $transformer->getExtUpload());
	}

/**
 * Tests if BanchaRequestTransformer extracts the Client ID correctly from the request.
 *
 */
	public function testGetClientId() {
		$transformer = new BanchaRequestTransformer(array(
			'data' => array(array(
				'data' => array(
					'__bcid' => '123456',
					'other'  => 'recordFields',
				),
			)),
		));
		$this->assertNotNull($transformer->getClientId());
		$this->assertEquals('123456', $transformer->getClientId());
	}

/**
 * If the Ext JS request contains an URL, we need to extract it from the request, because we need to pass it to the
 * Constructor of CakeRequest.
 *
 */
	public function testGetUrl() {
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
	public function testGetPassParams() {
		$input = array(
				'method'	=> 'update',
				'data'		=> array(array('data'=>array('id' => 42))),
		);
		$transformer = new BanchaRequestTransformer($input);
		$this->assertEquals(array('id' => 42), $transformer->getPassParams());
	}

/**
 * Same as {@see testGetPassParams()} but for form request.
 *
 */
	public function testGetPassParamsForm() {
		$transformer = new BanchaRequestTransformer(array(
			'extAction'	=> 'Test',
			'extMethod'	=> 'update',
			'extTID'	=> 'update',
			'id' => 42,
		));
		$this->assertEquals(array('id' => 42), $transformer->getPassParams());
	}

/**
 * For remotable methods the parameter which need to be passed to the method are sent a little bit different than for
 * CRUD actions.
 */
	public function testGetPassParamsRemotable() {
		$input = array(
			'data'	=> array('florian'),
			'type'	=> 'rpc',
		);
		$transformer = new BanchaRequestTransformer($input);
		$this->assertEquals(array('florian'), $transformer->getPassParams());
	}

/**
 * Ext JS uses page, offset, limit and sort in the data array for pagination. CakePHP needs a paging array with
 * page, limit and order. The sort in Ext looks like [property: X, direction: Y], in Cake like [Controller.X => Y].
 *
 * @dataProvider getPagingProvider
 */
	public function testGetPaging($extData, $cakePaginate) {
		$data = array(
			'action'	=> 'Test',
			'data'		=> array($extData),
		);

		$transformer = new BanchaRequestTransformer($data);
		$paging = $transformer->getPaging();

		$this->assertEquals($paging['page'], $cakePaginate['page']);
		$this->assertEquals($paging['limit'], $cakePaginate['limit']);
		$this->assertEquals($paging['order'], $cakePaginate['order']);
		if(isset($cakePaginate['sort'])) {
			$this->assertEquals($paging['sort'], $cakePaginate['sort']);
		}
		if(isset($cakePaginate['direction'])) {
			$this->assertEquals($paging['direction'], $cakePaginate['direction']);
		}
	}

/**
 * Tests if the Transaction ID is correctly transformed.
 *
 */
	public function testGetTid() {
		$data = array(
			'tid'	=> 42,
		);

		$transformer = new BanchaRequestTransformer($data);
		$this->assertEquals(42, $transformer->getTid());
	}

/**
 * Same as {@see testGetTid()} but for form requests.
 *
 */
	public function testGetTidForm() {
		$data = array(
			'extTID'	=> 42,
		);

		$transformer = new BanchaRequestTransformer($data);
		$this->assertEquals(42, $transformer->getTid());
	}

/**
 * The data array (which represent POST parameters) in CakePHP only contains the actual data values but not the special
 * parameters. Thus we need to clean the data array from action, controller, paginate and pass parameters. We therefore
 * use the methods described and tested above.
 *
 */
	public function testGetCleanedDataArray() {
		$data = array(
			'action'	=> 'Test',
			'method'	=> 'read',
			'data'		=> array(array(
				'__bcid'	=> uniqid(),
				'id'		=> 42,
				'page'		=> 2,
				'limit'		=> 10,
				'sort'		=> array(),
				'foo'		=> 'bar'
			)),
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
		$this->assertFalse(isset($data['tid']));
		$this->assertFalse(isset($data['__bcid']));
		$this->assertEquals('bar', $data[0]['foo']);
	}

	public function testGetCleanedDataArrayForm() {
		$data = array(
			'extAction'	=> 'Test',
			'extMethod'	=> 'submit',
			'id'		=> 42,
			'foo'		=> 'bar',
			'extTID'	=> 1,
			'extUpload'	=> '1',
		);

		$transformer = new BanchaRequestTransformer($data);
		$data = $transformer->getCleanedDataArray();
		$this->assertFalse(isset($data['action']));
		$this->assertFalse(isset($data['method']));
		$this->assertFalse(isset($data['id']));
		$this->assertEquals('bar', $data['Test']['foo']);
		$this->assertFalse(isset($data['extTID']));
		$this->assertFalse(isset($data['extUpload']));
	}

/**
 * Provides the action names from Ext JS and CakePHP for use in testGetAction().
 *
 */
	public function getActionProvider() {
		return array(
			array('create',  array(),                                 'add'),
			array('update',  array(array('data'=>array('id' => 42))), 'edit'),
			array('destroy', array(array('data'=>array('id' => 42))), 'delete'),
			array('read',    array(array('data'=>array('id' => 42))), 'view'),
			array('read',    array(),                                 'index'),
			array('special', array(),                                 'special'), // non-standard crud actions stay the same
		);
	}
	
/**
 * Provides the action names from Ext JS and CakePHP for use in testGetActionForm().
 *
 */
	public function getActionFormProvider() {
		return array(
			array('submit',  array(),                                 'add'),
			array('submit',  array('id' => 42),                       'edit'),
			array('special', array(),                                 'special'), // non-standard crud actions stay the same
		);
	}

/**
 * Data provider for testGetRequestsPagination().
 *
 */
	public function getPagingProvider() {
		return array(
			// Default values
			array(
				array(),
				array( // defaults
					'page'		=> 1,
					'limit'		=> 500,
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
					'sort'      => 'title',
					'direction' => 'ASC'
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
