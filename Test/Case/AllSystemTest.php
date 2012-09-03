<?php
/**
 * AllSystemTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

/**
 * AllSystemTest class.
 *
 * This test group will run all test in the Bancha/Test/Case/System directory
 *
 * @package       Bancha
 * @category      tests
 */
class AllSystemTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('System tests');

		$path = dirname(__FILE__) . DS . 'System';
		$suite->addTestFile($path . DS . 'BanchaCrudTest.php');
		$suite->addTestFile($path . DS . 'BanchaDebugExceptionsTest.php');
		$suite->addTestFile($path . DS . 'BanchaExceptionsTest.php');
		$suite->addTestFile($path . DS . 'ConsistentModelTest.php');

		// $suite->addTestDirectory($path);
		return $suite;
	}
}