<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Network
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaResponseTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaResponseTransformerTest
 *
 * @package       Bancha.Test.Case.Network
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class BanchaResponseTransformerTest extends CakeTestCase {
	public $fixtures = array(
		'plugin.bancha.article',
		'plugin.bancha.category',
		'plugin.bancha.user',
		'plugin.bancha.tag',
		'plugin.bancha.articles_tag'
	);

/**
 * Test how the BanchaResponseTransformer handles primitive and non-primitive
 * results which are not tied to a model.
 *
 * @param $cakeResponse cake response to transform
 * @param $expectedResponse the expected sencha response
 *
 * @dataProvider transformNonModelRecordCasesDataProvider
 */
	public function testTransformNonModelRecordCases($cakeResponse, $expectedResponse) {
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue(isset($result['success']), 'Expected result to have a sucesss property, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Data provider for testTransformNonModelRecordCases
 */
	public function transformNonModelRecordCasesDataProvider() {
		return array(
			// primitive responses are the success value
			array( true, array('success'=>true) ),
			array( false, array('success'=>false) ),
			// responses with an success value are passed through
			array( array('success'=>true,'msg'=>'lala'), array('success'=>true,'msg'=>'lala') ), // there is a success, nothing to change
			array( array('success'=>true), array('success'=>true) ), // already in ext(-like) structure
			array( array('success'=>'true'), array('success'=>true) ), // already in ext(-like) structure, only convert property into a boolean
			array( array('success'=>'false'), array('success'=>false) ), // already in ext(-like) structure, only convert property into a boolean
			array( array('success'=>'true','message'=>'lala'), array('success'=>true,'message'=>'lala') ), // already in ext(-like) structure
			// arbitary data is wrapped into the data property
			array( 'lala', array('success'=>true,'data'=>'lala') ),
			array( -1, array('success'=>true,'data'=>-1) ),
			array( 0, array('success'=>true,'data'=>0) ),
			array( 1, array('success'=>true,'data'=>1) ),
			array( array('lala','lolo'), array('success'=>true,'data' => array('lala','lolo')) ),
		);
	}

/**
 * Test how the BanchaResponseTransformer handles objects.
 * This is a strange case, expect this should be the data.
 */
	public function testTransformObjectResults() {
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$cakeResponse = new stdClass();
		$cakeResponse->someProperty = 'someValue';

		// create a similar object
		$expectedResponse = new stdClass();
		$expectedResponse->someProperty = 'someValue';

		// now build the expected response
		$expectedResponse = array(
			'success' => true,
			'data' => $expectedResponse
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue(isset($result['success']), 'Expected result to have a sucsess property, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for a single returned record
 */
	public function testTransformSingleRecord() {
		// Response generated by CakePHP.
		$cakeResponse = array(
			'Article' => array(
				'id'        => 304,
				'title'     => 'foo',
				'date'      => '2011-11-21 01:50:00',
				'body'      => 'This is the text for foo',
				'published' => false,
				'user_id'   => 95
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				'id'        => 304,
				'title'     => 'foo',
				'date'      => '2011-11-21 01:50:00',
				'body'      => 'This is the text for foo',
				'published' => false,
				'user_id'   => 95
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for a single returned record
 * with filtering of model fields
 */
	public function testTransformSingleRecord_Filtered() {
		// Response generated by CakePHP.
		$cakeResponse = array(
			'Article' => array(
				'id'        => 304,
				'title'     => 'foo',
				'date'      => '2011-11-21 01:50:00', // should not be in result set
				'body'      => 'This is the text for foo',
				'published' => false,
				'user_id'   => 95 // should not be in result set
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller' => 'Articles',
			'action'     => 'view'
		));

		// Configure the model to not expose date and user_id
		$TestModel = ClassRegistry::init('Article');
		$TestModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('date', 'user_id')
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data'    => array(
				'id'        => 304,
				'title'	    => 'foo',
				'body'      => 'This is the text for foo',
				'published' => false
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for multiple return records
 *
 */
	public function testTransformMultipleRecords() {
		// Response generated by CakePHP
		$cakeResponse = array(
			array(
				'Article' => array(
					'id'        => 304,
					'title'     => 'foo',
					'date'      => '2011-11-21 01:50:00',
					'body'      => 'This is the text for foo',
					'published' => false,
					'user_id'   => 95
				),
			),
			array(
				'Article' => array(
					'id'        => 305,
					'title'	    => 'bar',
					'date'      => '2011-12-21 01:50:00',
					'body'      => 'This is the text for bar',
					'published' => false,
					'user_id'   => 95
				),
			)
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller' => 'Articles',
			'action'     => 'index',
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				array(
					'id'        => 304,
					'title'     => 'foo',
					'date'      => '2011-11-21 01:50:00',
					'body'      => 'This is the text for foo',
					'published' => false,
					'user_id'   => 95
				),
				array(
					'id'        => 305,
					'title'     => 'bar',
					'date'      => '2011-12-21 01:50:00',
					'body'      => 'This is the text for bar',
					'published' => false,
					'user_id'   => 95
				),
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for multiple return records
 * with filtering of model fields
 */
	public function testTransformMultipleRecords_Filtered() {
		// Response generated by CakePHP
		$cakeResponse = array(
			array(
				'Article' => array(
					'id'        => 304,
					'title'     => 'foo',
					'date'      => '2011-11-21 01:50:00', // should not be in result set
					'body'      => 'This is the text for foo',
					'published' => false,
					'user_id'   => 95 // should not be in result set
				),
			),
			array(
				'Article' => array(
					'id'        => 305,
					'title'     => 'bar',
					'date'      => '2011-12-21 01:50:00', // should not be in result set
					'body'      => 'This is the text for bar',
					'published' => false,
					'user_id'   => 95 // should not be in result set
				),
			)
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller' => 'Articles',
			'action'     => 'index',
		));

		// Configure the model to not expose date and user_id
		$TestModel = ClassRegistry::init('Article');
		$TestModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('date', 'user_id')
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				array(
					'id'        => 304,
					'title'     => 'foo',
					'body'      => 'This is the text for foo',
					'published' => false,
				),
				array(
					'id'        => 305,
					'title'     => 'bar',
					'body'      => 'This is the text for bar',
					'published' => false,
				),
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}


/**
 * Bancha understands cake responses with pagination data
 * @param $paginatedRecords cake response to transform
 * @param $expectedResponse Response expected by Ext JS (in JSON).
 *
 * @dataProvider getCakeRecords
 */
	public function testTransformPaginated($paginatedRecords, $expectedResponse) {
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$result = BanchaResponseTransformer::transform($paginatedRecords, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

	// data provider
	public function getCakeRecords() {
		return array(
			array(
				array('count'=>0,'records'=>array()),
				array('success'=>true,'total'=>0,'data' => array())
			),
			array(
				array('count'=>9,'records'=>array(
					array('Article'=>array('id'=>5,'title'=>'whatever')),
					array('Article'=>array('id'=>6,'title'=>'whatever2'))
				)),
				array('success'=>true,'total'=>9,'data' => array(
					array('id'=>5,'title'=>'whatever'),
					array('id'=>6,'title'=>'whatever2')
				)),
			)
		);
	}

/**
 * Bancha understands cake responses with pagination data.
 * Filtering should also be applied here.
 */
	public function testTransformPaginated_Filtered() {
		// testup
		$paginatedRecords = array(
			'count'   => 9,
			'records' => array(
				array(
					'Article'=>array(
						'id'        => 304,
						'title'     => 'foo',
						'date'      => '2011-11-21 01:50:00', // should not be in result set
						'body'      => 'This is the text for foo',
						'published' => false,
						'user_id'   => 95 // should not be in result set
					),
				),
				array(
					'Article'       => array(
						'id'        => 305,
						'title'	    => 'bar',
						'date'      => '2011-12-21 01:50:00', // should not be in result set
						'body'      => 'This is the text for bar',
						'published' => false,
						'user_id'   => 95 // should not be in result set
					)
				)
			)
		);

		$expectedResponse = array(
			'success' => true,
			'total'   => 9,
			'data'    => array(
				array(
					'id'        => 304,
					'title'     => 'foo',
					'body'      => 'This is the text for foo',
					'published' => false,
				),
				array(
					'id'        => 305,
					'title'     => 'bar',
					'body'      => 'This is the text for bar',
					'published' => false,
				),
			),
		);

		// create request to pass through
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		// Configure the model to not expose date and user_id
		$TestModel = ClassRegistry::init('Article');
		$TestModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('date', 'user_id')
		));

		$result = BanchaResponseTransformer::transform($paginatedRecords, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for associated data
 */
	public function testTransformSingleRecord_AssociatedData() {

		// Article 1001 belongsTo User 95
		// Article has many Tags: CakePHP 1, Bancha 2
		$articleModel = ClassRegistry::init('Article');
		$articleModel->recursive = 3;
		$articleModel->read(null,1001); // get the input

		// define excluded fields to test filtering
		$articleModel->Behaviors->load('Bancha.BanchaRemotable', array());
		$userModel = ClassRegistry::init('User');
		$userModel->Behaviors->load('Bancha.BanchaRemotable', array());
		$tagModel = ClassRegistry::init('Tag');
		$tagModel->Behaviors->load('Bancha.BanchaRemotable', array());

		// prepare
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		// execute
		$result = BanchaResponseTransformer::transform($articleModel->data, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));

		// test that the single record data is present
		$this->assertEquals(1001, $result['data']['id']);
		$this->assertEquals('Title 1', $result['data']['title']);

		// test that the belongsTo User record data is present
		$this->assertEquals(95, $result['data']['user']['id']);
		$this->assertEquals('mariano', $result['data']['user']['login']);

		// test that the hasMany Tag record data is present
		$this->assertCount(2, $result['data']['tags']);
		$this->assertEquals(1, $result['data']['tags'][0]['id']);
		$this->assertEquals('CakePHP', $result['data']['tags'][0]['string']);
		$this->assertEquals(2, $result['data']['tags'][1]['id']);
		$this->assertEquals('Bancha', $result['data']['tags'][1]['string']);
	}
/**
 * Tests the transform() method for associated data, check that filtering is applied
 */
	public function testTransformSingleRecord_AssociatedData_Filtered() {

		// Article 1001 belongsTo User 95
		// Article has many Tags: CakePHP 1, Bancha 2
		$articleModel = ClassRegistry::init('Article');
		$articleModel->read(null,1001); // get the input

		// define excluded fields to test filtering
		$articleModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('date')
		));
		$userModel = ClassRegistry::init('User');
		$userModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('password')
		));
		$tagModel = ClassRegistry::init('Tag');
		$tagModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('string')
		));

		// prepare
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		// execute
		$result = BanchaResponseTransformer::transform($articleModel->data, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));

		// test that the single record data is present
		$this->assertEquals(1001, $result['data']['id']);
		$this->assertEquals('Title 1', $result['data']['title']);
		$this->assertFalse(isset($result['data']['date'])); //excluded

		// test that the belongsTo User record data is present
		$this->assertEquals(95, $result['data']['user']['id']);
		$this->assertEquals('mariano', $result['data']['user']['login']);
		$this->assertFalse(isset($result['data']['user']['password'])); //excluded

		// test that the hasMany Tag record data is present
		$this->assertCount(2, $result['data']['tags']);
		$this->assertEquals(1, $result['data']['tags'][0]['id']);
		$this->assertFalse(isset($result['data']['tags'][0]['string']));
		$this->assertEquals(2, $result['data']['tags'][1]['id']);
		$this->assertFalse(isset($result['data']['tags'][1]['string']));

		// edge case: associated model is not exposed via Bancha
		$articleModel->Behaviors->load('Bancha.BanchaRemotable', array());
		$tagModel->Behaviors->unload('Bancha.BanchaRemotable');
		$userModel->Behaviors->unload('Bancha.BanchaRemotable');

		// execute
		$result = BanchaResponseTransformer::transform($articleModel->data, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));

		// test that user and tags are hidden
		$this->assertFalse(isset($result['data']['tags']));
		$this->assertFalse(isset($result['data']['user']));
	}

/**
 * Tests the transform() method for associated data
 */
	public function testTransformSingleRecord_AssociatedData_DeepAssociations() {

		// Article 1001 belongsTo User 95
		// Article has many Tags: CakePHP 1, Bancha 2
		// Article.User has two articles 1001 and 1002
		$articleModel = ClassRegistry::init('Article');
		$articleModel->recursive = 3;
		$articleModel->read(null,1001); // get the input

		// prepare
		$articleModel->Behaviors->load('Bancha.BanchaRemotable', array());
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		// execute
		$result = BanchaResponseTransformer::transform($articleModel->data, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		
		// test that the single record data is present
		$this->assertEquals(1001, $result['data']['id']);
		$this->assertEquals('Title 1', $result['data']['title']);

		// test that the belongsTo User record data is present
		$this->assertEquals(95, $result['data']['user']['id']);
		$this->assertEquals('mariano', $result['data']['user']['login']);

		// test that the hasAndBelongsToMany Tag records data are present
		$this->assertEquals(2, count($result['data']['tags']));
		$this->assertEquals(1, $result['data']['tags'][0]['id']);
		$this->assertEquals('CakePHP', $result['data']['tags'][0]['string']);
		$this->assertEquals(2, $result['data']['tags'][1]['id']);
		$this->assertEquals('Bancha', $result['data']['tags'][1]['string']);

		// test that the Article.User has two Article records
		$this->assertEquals(1001, $result['data']['user']['articles'][0]['id']);
		$this->assertEquals('Title 1', $result['data']['user']['articles'][0]['title']);
		$this->assertEquals(1002, $result['data']['user']['articles'][1]['id']);
		$this->assertEquals('Title 2', $result['data']['user']['articles'][1]['title']);

		// test that the Article.User.Article's have one User record each
		$this->assertEquals(95, $result['data']['user']['articles'][0]['user']['id']);
		$this->assertEquals('mariano', $result['data']['user']['articles'][0]['user']['login']);
		$this->assertEquals(95, $result['data']['user']['articles'][1]['user']['id']);
		$this->assertEquals('mariano', $result['data']['user']['articles'][1]['user']['login']);

		// test that the hasAndBelongsToMany Article.User.Article[0].Tag records data are present
		$this->assertEquals(2, count($result['data']['user']['articles'][0]['tags']));

		// test that the hasAndBelongsToMany Article.User.Article[1].Tag empty array is present
		$this->assertTrue(isset($result['data']['user']['articles'][1]['tags']));
		$this->assertEquals(0, count($result['data']['user']['articles'][1]['tags']));
	}

/**
 * Tests the transform() method for associated data for multiple records
 */
	public function testTransformMultiRecordSet_AssociatedData() {

		// Article 1001 belongsTo User 95
		// Article has many Tags: CakePHP 1, Bancha 2
		$articleModel = ClassRegistry::init('Article');
		$input = $articleModel->find('all'); // get the input

		// prepare
		$articleModel->Behaviors->load('Bancha.BanchaRemotable', array());
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		$result = BanchaResponseTransformer::transform($input, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		
		// test one level of nesting, rest should be the same as in the test above
		$this->assertEquals(103, count($result['data']));

		// test that the single record data is present
		$this->assertEquals(1001, $result['data'][0]['id']);
		$this->assertEquals('Title 1', $result['data'][0]['title']);

		// test that the belongsTo User record data is present
		$this->assertEquals(95, $result['data'][0]['user']['id']);
		$this->assertEquals('mariano', $result['data'][0]['user']['login']);

		// test that the hasAndBelongsToMany Tag records data are present
		$this->assertEquals(2, count($result['data'][0]['tags']));
		$this->assertEquals(1, $result['data'][0]['tags'][0]['id']);
		$this->assertEquals('CakePHP', $result['data'][0]['tags'][0]['string']);
		$this->assertEquals(2, $result['data'][0]['tags'][1]['id']);
		$this->assertEquals('Bancha', $result['data'][0]['tags'][1]['string']);
	}

/**
 * Tests the transform() method for threaded data with multiple records
 */
	public function testTransformThreadedRecordSet() {

		// response "generated" by CakePHP
		$cakeResponse = array(
			array(
				'Category' => array(
					'name' => 'My Categories',
					'id' => 1,
					'parent_id' => null,
					'lft' => 1,
					'rght' => 30,
				),
				'User' => array( // include associated data in the test
					'id' => 1,
					'login' => 'mario',
					'password' => 'this should be excluded'
				),
				'children' => array(
					array(
						'Category' => array(
							'name' => 'Fun',
							'id' => 2,
							'parent_id' => 1,
							'lft' => 2,
							'rght' => 15,
						),
						'Article' => array( // included associated records sets
							array(
								'id' => 1,
								'title' => 'My Article 1',
							),
							array(
								'id' => 2,
								'title' => 'My Article 2',
							)
						),
						'children' => array(
							array(
								'Category' => array(
									'name' => 'Sport',
									'id' => 3,
									'parent_id' => 2,
									'lft' => 3,
									'rght' => 8,
								),
								'children' => array()
							),
						)
					),
					array(
						'Category' => array(
							'name' => 'Work',
							'id' => 9,
							'parent_id' => 1,
							'lft' => 16,
							'rght' => 29,
						),
						'children' => array()
					)
				)
			)
		);

		// load the models and behaviors
		$categoryModel = ClassRegistry::init('Category');
		$categoryModel->Behaviors->load('Bancha.BanchaRemotable');
		$userModel = ClassRegistry::init('User');
		$userModel->Behaviors->load('Bancha.BanchaRemotable', array(
			'excludedFields' => array('password')
		));
		$articleModel = ClassRegistry::init('Article');
		$articleModel->Behaviors->load('Bancha.BanchaRemotable');

		$request = new CakeRequest();
		$request->addParams(array(
			'controller' => 'Categories',
			'action'     => 'index',
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				array(
					'name' => 'My Categories',
					'id' => 1,
					'parent_id' => null,
					'lft' => 1,
					'rght' => 30,
					'user' => array( // include associated data in the test
						'id' => 1,
						'login' => 'mario'
					),
					'data' => array(
						array(
							'name' => 'Fun',
							'id' => 2,
							'parent_id' => 1,
							'lft' => 2,
							'rght' => 15,
							'articles' => array( // included associated records sets
								array(
									'id' => 1,
									'title' => 'My Article 1',
								),
								array(
									'id' => 2,
									'title' => 'My Article 2',
								)
							),
							'data' => array(
								array(
									'name' => 'Sport',
									'id' => 3,
									'parent_id' => 2,
									'lft' => 3,
									'rght' => 8,
									'leaf' => true // instead of the empty children array
								),
							)
						),
						array(
							'name' => 'Work',
							'id' => 9,
							'parent_id' => 1,
							'lft' => 16,
							'rght' => 29,
							'leaf' => true // instead of the empty children array
						)
					)
				)
			)
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}
}
