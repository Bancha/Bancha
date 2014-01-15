<?php
/**
 * ArticlesController file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */
App::uses('AppController', 'Controller');

/**
 * Articles Controller
 *
 * @package       Bancha.Test.Case.System
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class ArticlesController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Article->recursive = -1; // modified, cause we don't need associated data
		$articles = $this->paginate();																// added
		$this->set('articles', $articles);															// modified
		return array_merge($this->request['paging']['Article'],array('records'=>$articles)); 		// added
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
		$this->set('article', $this->Article->read(null, $id));
		return $this->Article->data;																// added
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Article->create();

			if (isset($this->request->params['isBancha']) && $this->request->params['isBancha']) return $this->Article->saveFieldsAndReturn($this->request->data);	 // added

			if ($this->Article->save($this->request->data)) {
				$this->Session->setFlash(__('The article has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
			}
		}
		$users = $this->Article->User->find('list');
		$tags = $this->Article->Tag->find('list');
		$this->set(compact('users', 'tags'));
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
			if ($this->Article->save($this->request->data)) {
				//$this->Session->setFlash(__('The article has been saved'));
				//$this->redirect(array('action' => 'index'));
			} else {
				//$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Article->read(null, $id);
		}

		if (defined('SLEEP_TIME')) {
			//echo "\n\nSLEEP for " . SLEEP_TIME . " SECONDS\n\n";
			sleep(SLEEP_TIME);
		}

		return $this->Article->getLastSaveResult();
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

		if (isset($this->request->params['isBancha']) && $this->request->params['isBancha']) return $this->Article->deleteAndReturn();	 // added

		if ($this->Article->delete()) {
			$this->Session->setFlash(__('Article deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Article was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
