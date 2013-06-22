<?php
/**
 * AllRoutingTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

/**
 * AllRoutingTest class.
 *
 * This test group will run all test in the Bancha/Test/Cases/Routing directory
 *
 * @package       Bancha
 * @category      tests
 */
class AllRoutingTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('Routing classes');

		$path = dirname(__FILE__) . DS . 'Routing';
		$suite->addTestFile($path . DS . 'BanchaDispatcherTest.php');

		$suite->addTestDirectory($path);
		return $suite;
	}
}
