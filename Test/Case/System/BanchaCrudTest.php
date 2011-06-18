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
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaCrudTest
 *
 * @package bancha.libs
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

		$this->assertNotNull($responses[0]->data->id);
		$this->assertEquals('Hello World', $responses[0]->data->title);
		$this->assertEquals(false, $responses[0]->data->published);
		$this->assertEquals(1, $responses[0]->data->user_id);
		
		// Clean up operations: delete article
		$article = new Article();
		$article->id = $responses[0]->data->id;
		$article->delete();
	}
	
	public function testEdit() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));
		
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
		
		$this->assertEquals($article->id, $responses[0]->data->id);
		$this->assertEquals('foobar', $responses[0]->data->title);
		$this->assertEquals(true, $responses[0]->data->published);
		
		// Clean up operations: delete article
		$article->delete();
	}
	
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
		
		$this->assertEquals(array(), $responses[0]->data);
	}
	
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
		
		$this->assertEquals(2, count($responses[0]->data));
		$this->assertEquals($article1->id, $responses[0]->data[0]->id);
		$this->assertEquals($article2->id, $responses[0]->data[1]->id);
		
		// Clean up operations: delete articles
		$article1->delete();
		$article2->delete();
		$article3->delete();
		$article4->delete();
	}
	
	public function testView() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));
		
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
		
		$this->assertEquals($article->id, $responses[0]->data[0]->id);
		$this->assertEquals('foo', $responses[0]->data[0]->title);
		
		// Clean up operations: delete article
		$article->delete();
	}
	
}

/**
 * Articles Controller
 *
 */
class ArticlesController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Article->recursive = 0;
		$data = $this->paginate();
		foreach ($data as $i => $element)
		{
			$data[$i] = $element['Article'];
		}
		return $data;
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		$data = $this->Article->read(null, $id);
		return array($data['Article']);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Article->create();
			if ($data = $this->Article->save($this->request->data)) {
				$data['Article']['id'] = $this->Article->id;
				return $data['Article'];
			} else {
			}
		}
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($data = $this->Article->save($this->request->data)) {
				$data['Article']['id'] = $id;
				return $data['Article'];
			} else {
			}
		} else {
		}
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		if ($this->Article->delete()) {
			return array();
		}
	}

}

