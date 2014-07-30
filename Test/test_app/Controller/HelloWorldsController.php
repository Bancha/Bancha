<?php
/**
 * HelloWorldsController file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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
 * Returns the data 'Hello World'.
 * 
 * @return String the greeting
 * @banchaRemotable
 */
	public function hello() {
		return array('data' => 'Hello World');
	}

/**
 * Greets two people by name.
 * 
 * @param string $name the first person
 * @param string $name2 the second person
 * @return string the greeting
 * @banchaRemotable
 */
	public function helloyou($name, $name2) {
		return array('data' => sprintf('Hello %s and %s!', $name, $name2));
	}

}
