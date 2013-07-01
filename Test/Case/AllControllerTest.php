<?php
/**
 * AllControllerTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
 * @package       Bancha.Test.Case
 * @since         Bancha v 0.9.0
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
		$suite->addTestFile($path . DS . 'Component' . DS . 'BanchaPaginatorComponentTest.php');

		$suite->addTestDirectory($path);
		return $suite;
	}
}
