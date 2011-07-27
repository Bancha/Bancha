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
 * Tests the 'add' CRUD operation using the full CakePHP stack.
 *
 */
	public function testAdd() {
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'create',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertNotNull($responses[0]->result->id);
		$this->assertEquals('Hello World', $responses[0]->result->title);
		$this->assertEquals(false, $responses[0]->result->published);
		$this->assertEquals(1, $responses[0]->result->user_id);
		$this->assertEquals(1, $responses[0]->tid);

		// Clean up operations: delete article
		$article = new Article();
		$article->id = $responses[0]->result->id;
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
			'action'		=> 'Articles',
			'method'		=> 'update',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'id'			=> $article->id,
				'title'			=> 'foobar',
				'published'		=> true,
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals($article->id, $responses[0]->result->id);
		$this->assertEquals('foobar', $responses[0]->result->title);
		$this->assertEquals(true, $responses[0]->result->published);
		$this->assertEquals(1, $responses[0]->tid);

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
			'action'		=> 'Articles',
			'method'		=> 'destroy',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('id' => $article->id)
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals(array(), $responses[0]->result);
		$this->assertEquals(1, $responses[0]->tid);
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
		$article1->save(array('title' => 'foo'));
		$article2 = new Article();
		$article2->create();
		$article2->save(array('title' => 'bar'));
		$article3 = new Article();
		$article3->create();
		$article3->save(array('title' => 'foobar'));
		$article4 = new Article();
		$article4->create();
		$article4->save(array('title' => 'hello world'));

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'limit'			=> 2
			),
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals(2, count($responses[0]->result));

		$this->assertEquals($article1->id, $responses[0]->result[0]->id);
		$this->assertEquals($article2->id, $responses[0]->result[1]->id);
		$this->assertEquals(1, $responses[0]->tid);

		// Clean up operations: delete articles
		$article1->delete();
		$article2->delete();
		$article3->delete();
		$article4->delete();
	}

/**
 * Tests the view action. We need to create an article, which we need to delete after the test.
 *
 */
	public function testView() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Build a HTTP request that looks like in Ext JS.
		$rawPostData = json_encode(array(
			'action'		=> 'Articles',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('id' => $article->id)
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		$this->assertEquals($article->id, $responses[0]->result[0]->id);
		$this->assertEquals('foo', $responses[0]->result[0]->title);
		$this->assertEquals(1, $responses[0]->tid);

		// Clean up operations: delete article
		$article->delete();
	}

}
