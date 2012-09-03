<?php
/**
 * AllControllerTest file.
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
 * AllControllerTest class.
 *
 * This test group will run all test in the Bancha/Test/Cases/Controller directory
 *
 * @package       Bancha
 * @category      tests
 */
class AllControllerTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('Controller classes');

		$path = dirname(__FILE__) . DS . 'Controller';
		$suite->addTestFile($path . DS . 'BanchaControllerTest.php');

		$suite->addTestDirectory($path);
		return $suite;
	}
}
