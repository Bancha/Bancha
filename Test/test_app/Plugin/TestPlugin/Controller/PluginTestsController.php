<?php
/**
 * PluginTestsController file.
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

/**
 * PluginTests Controller
 *
 * @package       Bancha.Test.test_app.Plugin.TestPlugin.Controller
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.1.0
 */
class PluginTestsController extends TestPluginAppController {

	public $uses = array();

/**
 * This is an exposed method, which does nothing.
 * 
 * @banchaRemotable
 * @return void nothing
 */
	public function exposedTestMethod() {
	}
}
