<?php
/**
 * CategoriesController file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.test_app.Controller
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.2.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
App::uses('Controller', 'Controller');
App::uses('AppController', 'Controller');

/**
 * Categories Controller
 *
 * @package       Bancha.Test.test_app.Controller
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.2.0
 */
class CategoriesController extends AppController {

/**
 * Returns threaded tree data from the category model.
 *
 * @return array the data.
 */
	public function index() {
		return $this->Category->find('threaded');
	}
}
