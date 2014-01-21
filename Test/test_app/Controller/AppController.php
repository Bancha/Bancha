<?php
/**
 * AppController file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
App::uses('Controller', 'Controller');

/**
 * AppController
 *
 * @package       Bancha.Test.test_app.Plugin.TestPlugin.Controller
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class AppController extends Controller {

/**
 * Use the BanchaPaginatorComponent to also support pagination
 * and remote searching for Sencha Touch and Ext JS stores
 */
	public $components = array('Session', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));
}
