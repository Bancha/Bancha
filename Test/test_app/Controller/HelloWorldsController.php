<?php
/**
 * HelloWorldsController file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
App::uses('Controller', 'Controller');
App::uses('AppController', 'Controller');

/**
 * HelloWorlds Controller
 *
 * @package       Bancha.Test.test_app.Plugin.TestPlugin.Controller
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.1.0
 */
class HelloWorldsController extends AppController {

/**
 * @banchaRemotable
 */
	public function hello() {
		return array('data' => 'Hello World');
	}

/**
 * Greets two people by name.
 * @banchaRemotable
 * @param String $name the first person
 * @param String $name2 the second person
 * @return String the greeting
 */
	public function helloyou($name, $name2) {
		return array('data' => sprintf('Hello %s and %s!', $name, $name2));
	}

}
