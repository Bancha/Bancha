<?php
/**
 * BanchaCrudTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

// TODO: refactor to use real test models.
require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaCrudTest
 *
 * All these tests are using the full stack of CakePHP components, not only testing 
 * the functionallity of Bancha, but also that it is compatible to the current
 * CakePHP library (since bancha is using some internal methods)
 * 
 * @package       Bancha
 * @category      Tests
 */
class BanchaCrudTest extends CakeTestCase {
	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	public function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}


	public function testAdd() {
		
		$config = ConnectionManager::enumConnectionObjects();
		$this->skipIf(
			$config['default']['datasource'] ===  'Database/Sqlite',
			'Default database needs to be persistent for this test' 
		);

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'create',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			))),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertNotNull($responses[0]->result->data->id);
		$this->assertEquals('Hello World', $responses[0]->result->data->title);
		$this->assertEquals(false, $responses[0]->result->data->published);
		$this->assertEquals(1, $responses[0]->result->data->user_id);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('create', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// Clean up operations: delete article
		$article = new Article();
		$article->id = $responses[0]->result->data->id;
		$article->delete();
	}

	public function testEdit() {
		// used fixture:
		// array('id' => 988, 'title' => 'Title 1', 'published' => true, ...)
		
		// Buld a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'id'			=> 988,
				'title'			=> 'foobar',
				'published'		=> 1,
			))),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
	
		$this->assertEquals(988, $responses[0]->result->data->id);
		$this->assertEquals('foobar', $responses[0]->result->data->title);
		$this->assertEquals(1, $responses[0]->result->data->published); // check that all fields are added
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('update', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}
	
	/**
	 * test form submission including the different request form of ext.direct
	 */
	public function testSubmit() {
		// used fixture:
		// array('id' => 988, 'title' => 'Title 1', 'published' => true, ...)

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> 988,
			'body'			=> 'changed',
			
			// ext stuff
			'extTID'		=> 1,
			'extAction'		=> 'Article',
			'extMethod'		=> 'submit',
			'extType'		=> 'rpc',
		);
		
		// it's no upload, so use the default way to decode the response
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection('',$postData), array('return' => true)
		));
		
		// test data (expected in default ext structure)
		$this->assertEquals(988, $responses[0]->result->data->id);
		$this->assertEquals('Title 1', $responses[0]->result->data->title); // expect the full record in the answer
		$this->assertEquals('changed', $responses[0]->result->data->body); // expect body to be changed
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('submit', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// test if the data really got changed
		$article = ClassRegistry::init('Article');
		$article->read(null,988);
		$this->assertEquals('changed', $article->data['Article']['body']);
	}
	
	
	public function testSubmit_WithUpload() {
		// used fixture:
		// array('id' => 988, 'title' => 'Title 1', 'body' => 'Text 3, ...)

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> 988,
			'body'			=> 'changed',
			
			// ext stuff
			'extTID'		=> 1,
			'extAction'		=> 'Article',
			'extMethod'		=> 'submit',
			'extType'		=> 'rpc',
			'extUpload'		=> true,  // <----------------- this time it's an upload
		);
		$dispatcher = new BanchaDispatcher();
		$result = $dispatcher->dispatch(
			new BanchaRequestCollection('',$postData), array('return' => true)
		);

		// the response should be surounded by some html (because of the upload)
		$this->assertEquals(1,preg_match("/\<html\>\<body\>\<textarea\>(.*)\<\/textarea\>\<\/body\>\<\/html\>/",$result));

		// decode by excluding the html part
		$responses = json_decode(substr($result,22,-25));
		
		// test data (expected in default ext structure)
		$this->assertEquals(988, $responses[0]->result->data->id);
		$this->assertEquals('Title 1', $responses[0]->result->data->title); // expect the full record in the answer
		$this->assertEquals('changed', $responses[0]->result->data->body); // expect body to be changed
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('submit', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// test if the data really got changed
		$article = ClassRegistry::init('Article');
		$article->read(null,988);
		$this->assertEquals('changed', $article->data['Article']['body']);
	}
	
	
	public function testDelete() {
		// Preparation: create article
		// used fixture:
		// array('id' => 988, 'title' => 'Title 1', ...)

		// deletion as ext.direct request
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array('id' => 988)))
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// test result
		$this->assertEquals(true, $responses[0]->result->success);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('destroy', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
		
		// test if the record really got deleted
		$article = ClassRegistry::init('Article');
		$article->id = 988;
		$this->assertEquals(false, $article->exists());
	}


	public function testIndex() {
		// used fixtures:
		// array('id' => 988, 'title' => 'Title 1', ...)
		// array('id' => 989, 'title' => 'Title 2', ...)
		// array('id' => 990, 'title' => 'Title 3', ...)
	
	
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 1,
				'limit'			=> 2,
			)),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// test data
		
		// only first and second element should be loaded
		$this->assertEquals(2, count($responses[0]->result->data));
		$this->assertEquals(988, $responses[0]->result->data[0]->id);
		$this->assertEquals(989, $responses[0]->result->data[1]->id);
		
		// the counter should be 3
		$this->assertEquals(3, $responses[0]->result->total);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('read', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));



		// Test page two
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 2,
				'limit'			=> 2,
			)),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// test data

		// only third element should be loaded
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals(990, $responses[0]->result->data[0]->id);
		$this->assertEquals('Title 3', $responses[0]->result->data[0]->title);

		// the counter should be 3
		$this->assertEquals(3, $responses[0]->result->total);

		// tid should be passed through
		$this->assertEquals(2, $responses[0]->tid);
	}

/**
 * Test if the whole stack also works if no results for index exist
 */
	public function testIndex_Empty() {
		// delete fixtures
		$article = ClassRegistry::init('Article');
		$article->delete(988);
		$article->delete(989);
		$article->delete(990);

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 1,
				'limit'			=> 10,
			)),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// test empty data result
		$this->assertTrue(is_array($responses[0]->result->data));
		$this->assertEquals(0, count($responses[0]->result->data));
		
		// test success
		$this->assertTrue($responses[0]->result->success);

		// the counter should be 0
		$this->assertEquals(0, $responses[0]->result->total);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('read', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}


	public function testView() {
		// used fixtures:
		// array('id' => 988, 'title' => 'Title 1', ...)
		// array('id' => 989, 'title' => 'Title 2', ...)

		// load one record, in ExtJS syntax
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'data'	=> array('id' => 988)
			))
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// test data
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals(988, $responses[0]->result->data->id);
		$this->assertEquals('Title 1', $responses[0]->result->data->title);
		
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('read', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
		
		
		// now look for a second one
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'data'	=>array('id' => 989)
			))
		)));
		
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// test
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals(989, $responses[0]->result->data->id);
		$this->assertEquals('Title 2', $responses[0]->result->data->title);
		$this->assertEquals(2, $responses[0]->tid);
	}





/**
 * Test the bancha stack, especially the dispatching with a multi-request 
 *
 */
	public function testMultiRequest() {
		// used fixture:
		// array('id' => 988, 'title' => 'Title 1', 'published' => true, ...)
		
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'create',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			))),
		),array(
			'action'		=> 'Article',
			'method'		=> 'update',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'id'			=> 988,
				'title'			=> 'foobar',
				'published'		=> false,
			))),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// general response checks (check dispatcher, collections and transformers)
		$this->assertTrue(isset($responses[0]->result), 'Expected an action proptery on first response, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('create', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		
		$this->assertEquals('Article', $responses[1]->action);
		$this->assertEquals('update', $responses[1]->method);
		$this->assertEquals('rpc', $responses[1]->type);
		$this->assertEquals(2, $responses[1]->tid);
		
		$this->assertEquals(2, count($responses));
		

		// test data for first request
		$this->assertNotNull($responses[0]->result->data->id);
		$this->assertEquals('Hello World', $responses[0]->result->data->title);
		$this->assertEquals(false, $responses[0]->result->data->published);
		$this->assertEquals(1, $responses[0]->result->data->user_id);
		
		// test data for second request
		$this->assertEquals(988, $responses[1]->result->data->id);
		$this->assertEquals('foobar', $responses[1]->result->data->title);
		$this->assertEquals(0, $responses[1]->result->data->published);
		$this->assertEquals('Text 1', $responses[1]->result->data->body); // should be a full record
	}
}
