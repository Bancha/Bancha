<?php
/**
 * AllRoutingTest file
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       bancha.libs
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */
/**
 * AllUtiltiyTest class
 *
 * This test group will run all test in the Bancha/Test/Cases/Model/Behaviours directory
 *
 * @package       bancha.tests.groups
 */
class AllUtiltiyTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('Utility classes');

		$path = dirname(__FILE__) . DS . 'Utility';
		$suite->addTestFile($path . DS . 'ArrayConverterTest.php');

		$suite->addTestDirectory($path);
		return $suite;
	}
}
