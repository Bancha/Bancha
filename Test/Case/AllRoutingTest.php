<?php
/**
 * AllRoutingTest file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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
 * @package       Bancha.Test.Case
 * @since         Bancha v 0.9.0
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
