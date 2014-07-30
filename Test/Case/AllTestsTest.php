<?php
/**
 * AllTestsTest file.
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
 * AllTestsTest class
 *
 * This test group will run all fast tests in the Bancha/Test/Cases directory.
 *
 * @package       Bancha.Test.Case
 * @since         Bancha v 0.9.0
 */
class AllTestsTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Tests');

		$path = dirname(__FILE__) . DS;
		$suite->addTestFile($path . DS . 'AllBehaviorsTest.php');
		$suite->addTestFile($path . DS . 'AllControllerTest.php');
		$suite->addTestFile($path . DS . 'AllNetworkTest.php');
		$suite->addTestFile($path . DS . 'AllRoutingTest.php');
		$suite->addTestFile($path . DS . 'AllSystemTest.php');
		$suite->addTestFile($path . DS . 'Lib' . DS . 'BanchaApiTest.php');
		$suite->addTestFile($path . DS . 'Lib' . DS . 'CakeSenchaDataMapperTest.php');
		$suite->addTestFile($path . DS . 'Console' . DS . 'Command' . DS . 'Task' . DS . 'BanchaExtractTaskTest.php');

		//$suite->addTestDirectory($path);
		return $suite;
	}
}
