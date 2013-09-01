<?php
/**
 * BanchaCrudTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
 * @package       Bancha.Test.Case.System
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class BanchaCrudTest extends CakeTestCase {
	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	private $originalDebugLevel;

	public function setUp() {
		parent::setUp();

		$this->originalDebugLevel = Configure::read('debug');
	}

	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->originalDebugLevel);

		// clear the registry
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

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

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
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...)

		// Buld a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'id'			=> 1001,
				'title'			=> 'foobar',
				'published'		=> 1,
			))),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertEquals(1001, $responses[0]->result->data->id);
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
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...)

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> 1001,
			'body'			=> 'changed',

			// ext stuff
			'extTID'		=> 1,
			'extAction'		=> 'Article',
			'extMethod'		=> 'submit',
			'extType'		=> 'rpc',
		);

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection('', $postData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		// it's not an upload, so use the default way to decode the response
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify data (expected in default ext structure)
		$this->assertEquals(1001, $responses[0]->result->data->id);
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
		$article->read(null,1001);
		$this->assertEquals('changed', $article->data['Article']['body']);
	}


	public function testSubmit_WithUpload() {
		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', 'body' => 'Text 3, ...)

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> 1001,
			'body'			=> 'changed',

			// ext stuff
			'extTID'		=> 1,
			'extAction'		=> 'Article',
			'extMethod'		=> 'submit',
			'extType'		=> 'rpc',
			'extUpload'		=> true,  // <----------------- this time it's an upload
		);

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection('', $postData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$result = $dispatcher->dispatch($collection, $response, array('return' => true));

		// the response should be surounded by some html (because of the upload)
		$this->assertEquals(1,preg_match("/\<html\>\<body\>\<textarea\>(.*)\<\/textarea\>\<\/body\>\<\/html\>/",$result));

		// decode by excluding the html part
		$responses = json_decode(substr($result,22,-25));

		// verify data (expected in default ext structure)
		$this->assertEquals(1001, $responses[0]->result->data->id);
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
		$article->read(null,1001);
		$this->assertEquals('changed', $article->data['Article']['body']);
	}


	public function testDelete() {
		// Preparation: create article
		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', ...)

		// deletion as ext.direct request
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array('id' => 1001)))
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

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
		$article->id = 1001;
		$this->assertEquals(false, $article->exists());
	}


	public function testIndex() {

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

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check data

		// only first and second element should be loaded
		$this->assertEquals(2, count($responses[0]->result->data));
		$this->assertEquals(1001, $responses[0]->result->data[0]->id);
		$this->assertEquals(1002, $responses[0]->result->data[1]->id);

		// the counter should be 103 (see article fixture)
		$this->assertEquals(103, $responses[0]->result->total);

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
				'limit'			=> 100,
			)),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check data

		// only three elements should be loaded
		$this->assertEquals(3, count($responses[0]->result->data));
		$this->assertEquals(1101, $responses[0]->result->data[0]->id);
		$this->assertEquals('Title 101', $responses[0]->result->data[0]->title);
		$this->assertEquals(1102, $responses[0]->result->data[1]->id);
		$this->assertEquals('Title 102', $responses[0]->result->data[1]->title);
		$this->assertEquals(1103, $responses[0]->result->data[2]->id);
		$this->assertEquals('Title 103', $responses[0]->result->data[2]->title);

		// the counter should still be 103
		$this->assertEquals(103, $responses[0]->result->total);

		// tid should be passed through
		$this->assertEquals(2, $responses[0]->tid);
	}

/**
 * Test if the whole stack also works if no results for index exist
 */
	public function testIndex_Empty() {
		// delete all fixture entries
		$article = ClassRegistry::init('Article');
		$config = ConnectionManager::enumConnectionObjects();
		if($config['default']['datasource'] ===  'Database/Postgres') {
			// postgres requires this, because it doesn't support implizit conversion
			$article->deleteAll(array("1"=>"true"));
		} else {
			// while mysql understands both versions,
			// sqlilite requires this version
			$article->deleteAll(array("1"=>"1"));
		}

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

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

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
		// array('id' => 1001, 'title' => 'Title 1', ...)
		// array('id' => 1002, 'title' => 'Title 2', ...)

		// load one record, in ExtJS syntax
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'data'	=> array('id' => 1001)
			))
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify data
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals(1001, $responses[0]->result->data->id);
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
				'data'	=>array('id' => 1002)
			))
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals(1002, $responses[0]->result->data->id);
		$this->assertEquals('Title 2', $responses[0]->result->data->title);
		$this->assertEquals(2, $responses[0]->tid);
	}





/**
 * Test the bancha stack, especially the dispatching with a multi-request
 *
 */
	public function testMultiRequest() {
		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...)

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
				'id'			=> 1001,
				'title'			=> 'foobar',
				'published'		=> false,
			))),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

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


		// verify data for first request
		$this->assertNotNull($responses[0]->result->data->id);
		$this->assertEquals('Hello World', $responses[0]->result->data->title);
		$this->assertEquals(false, $responses[0]->result->data->published);
		$this->assertEquals(1, $responses[0]->result->data->user_id);

		// verify data for second request
		$this->assertEquals(1001, $responses[1]->result->data->id);
		$this->assertEquals('foobar', $responses[1]->result->data->title);
		$this->assertEquals(0, $responses[1]->result->data->published);
		$this->assertEquals('Text 1', $responses[1]->result->data->body); // should be a full record
	}
}
