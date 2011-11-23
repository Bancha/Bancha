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
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/../../../Model/Behavior/BanchaBehavior.php';

// TODO: refactor to use real test models.
require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaCrudTest
 *
 * @package       Bancha
 * @category      Tests
 */
class BanchaCrudTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}

	/**
	 * Please make sure that the project default database is empty!
	 */
	public function testTestSetUp() {
		$controller = new ArticlesController();
		$controller->loadModel("User");
		$this->assertEquals(0,count($controller->User->find('all')),"\n\n\n\nPlease make sure that the project default database User table is empty!\n\n\n\n");
		$this->assertEquals(0,count($controller->Article->find('all')),"\n\n\n\nPlease make sure that the project default database Article table is empty!\n\n\n\n");
	}
/**
 * Tests the 'add' CRUD operation using the full CakePHP stack.
 *
 */
	public function testAdd() {
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(
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
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
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

/**
 * Test the edit functionality using the full stack of CakePHP components. In preparation we need to create a dummy
 * article, which we need to delete at the end of the test case.
 *
 */
	public function testEdit() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Buld a request like it looks in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array(
				'id'			=> $article->id,
				'title'			=> 'foobar',
				'published'		=> true,
			))),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals($article->id, $responses[0]->result->data->id);
		$this->assertEquals('foobar', $responses[0]->result->data->title);
		$this->assertEquals(true, $responses[0]->result->data->published);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('update', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// Clean up operations: delete article
		$article->delete();
	}
	
/**
 * Test the load functionality using the full stack of CakePHP components. In preparation we need to create a dummy
 * article, which we need to delete at the end of the test case.
 *
 */
	public function testLoad() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo','body' => 'la la la'));

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles id
			'id'			=> $article->id,
			
			// ext stuff
			'extTID'		=> 1,
			'extAction'		=> 'Article',
			'extMethod'		=> 'load',
			'extType'		=> 'rpc',
		);
		
		// it's no upload, so use the default way to decode the response
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection('',$postData), array('return' => true)
		));
		
		// test data (expected in default ext structure)
		$this->assertEquals($article->id, $responses[0]->result->data->id);
		$this->assertEquals('foo', $responses[0]->result->data->title); // expect record data
		$this->assertEquals('la la la', $responses[0]->result->data->body); // expect record data
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('load', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
		
		// Clean up operations: delete article
		$article->delete();
	}
/**
 * Test the submit functionality using the full stack of CakePHP components. In preparation we need to create a dummy
 * article, which we need to delete at the end of the test case.
 *
 */
	public function testSubmit() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> $article->id,
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
		$this->assertEquals($article->id, $responses[0]->result->data->id);
		$this->assertEquals('foo', $responses[0]->result->data->title); // expect the full record in the answer
		$this->assertEquals('changed', $responses[0]->result->data->body); // expect body to be changed
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('submit', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// test if the data really got changed
		$article->read();
		$this->assertEquals('changed', $article->data['Article']['body']);
		
		// Clean up operations: delete article
		$article->delete();
	}
/**
 * Test the submit functionality using the full stack of CakePHP components. In preparation we need to create a dummy
 * article, which we need to delete at the end of the test case.
 *
 */
	public function testSubmit_WithUpload() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Buld a request like it looks in Ext JS for a form submit
		$postData = array(
			// articles data
			'id'			=> $article->id,
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
		$this->assertEquals($article->id, $responses[0]->result->data->id);
		$this->assertEquals('foo', $responses[0]->result->data->title); // expect the full record in the answer
		$this->assertEquals('changed', $responses[0]->result->data->body); // expect body to be changed
		$this->assertEquals(false, $responses[0]->result->data->published);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('submit', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// test if the data really got changed
		$article->read();
		$this->assertEquals('changed', $article->data['Article']['body']);

		// Clean up operations: delete article
		$article->delete();
	}
/**
 * Test deleting an entity using the full stack of CakePHP components. We need to create a test article, which we
 * need to delete at the end of the test case.
 *
 */
	public function testDelete() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Let's begin with the real test.
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array('id' => $article->id)))
		));
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
		$this->assertEquals(false, $article->exists());
	}

/**
 * Test the index action, which lists entities. Before the real test starts we need to create some articles, which we
 * delete after the test.
 *
 */
	public function testIndex() {
		// Preparation: create articles
		$article1 = new Article();
		$article1->create();
		$this->assertTrue(!!$article1->save(array('title' => 'foo')));
		$article2 = new Article();
		$article2->create();
		$this->assertTrue(!!$article2->save(array('title' => 'bar')));
		$article3 = new Article();
		$article3->create();
		$this->assertTrue(!!$article3->save(array('title' => 'foobar')));

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 1,
				'limit'			=> 2,
			)),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// test data
		
		// only first and second element should be loaded
		$this->assertEquals(2, count($responses[0]->result->data));
		$this->assertEquals($article1->id, $responses[0]->result->data[0]->id);
		$this->assertEquals($article2->id, $responses[0]->result->data[1]->id);
		
		// the counter should be 3
		$this->assertEquals(3, $responses[0]->result->total);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('read', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));



		// Test page two
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 2,
				'limit'			=> 2,
			)),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// test data

		// only third element should be loaded
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals($article3->id, $responses[0]->result->data[0]->id);

		// the counter should be 3
		$this->assertEquals(3, $responses[0]->result->total);

		// tid should be passed through
		$this->assertEquals(2, $responses[0]->tid);
		
		
		// Clean up operations: delete articles
		$article1->delete();
		$article2->delete();
		$article3->delete();
	}

/**
 * Tests the view action. We need to create an article, which we need to delete after the test.
 *
 */
	public function testView() {
		// Preparation: create articles
		$articleForCake = new Article(); // quite strange, but cake seems to use this object!???
		$article1 = new Article();
		$article1->create();
		$this->assertTrue(!!$article1->save(array('title' => 'foo')));
		$article2 = new Article();
		$article2->create();
		$this->assertTrue(!!$article2->save(array('title' => 'bar')));

		// Build a HTTP request that looks like in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array('id' => $article1->id)))
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// test data
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals($article1->id, $responses[0]->result->data->id);
		$this->assertEquals('foo', $responses[0]->result->data->title);
		
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('Article', $responses[0]->action);
		$this->assertEquals('read', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
		
		
		// now look for the other one
		// Build a HTTP request that looks like in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array('data'=>array('id' => $article2->id)))
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// test
		$this->assertEquals(1, count($responses[0]->result->data));
		$this->assertEquals($article2->id, $responses[0]->result->data->id);
		$this->assertEquals('bar', $responses[0]->result->data->title);
		$this->assertEquals(2, $responses[0]->tid);
		
		
		
		// Clean up operations: delete articles
		$article1->delete();
		$article2->delete();
	}

}
