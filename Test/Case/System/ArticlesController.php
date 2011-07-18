<?php

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
		$articles = $this->paginate();
		$this->set('articles', $articles);
		return $articles;
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
		$this->set('article', $data);
		return $data;
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$data = array();
		if ($this->request->is('post')) {
			$this->Article->create();
			if ($data = $this->Article->save($this->request->data)) {
				// $this->flash(__('Article saved.'), array('action' => 'index'));
				$data['Article']['id'] = $this->Article->id;
			} else {
			}
		}
		$users = $this->Article->User->find('list');
		$tags = $this->Article->Tag->find('list');
		$this->set(compact('users', 'tags'));
		return $data;
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
				// $this->flash(__('The article has been saved.'), array('action' => 'index'));
				$data['Article']['id'] = $id;
			} else {
			}
		} else {
			$this->request->data = $this->Article->read(null, $id);
		}
		$users = $this->Article->User->find('list');
		$tags = $this->Article->Tag->find('list');
		$this->set(compact('users', 'tags'));
		return $data;
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
			// $this->flash(__('Article deleted'), array('action' => 'index'));
			return array();
		}
		// $this->flash(__('Article was not deleted'), array('action' => 'index'));
		$this->redirect(array('action' => 'index'));
	}

}
