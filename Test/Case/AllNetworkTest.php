<?php
/**
 * AllNetworkTest file.
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
 * AllNetworkTest class.
 *
 * This test group will run all test in the Bancha/Test/Cases/Network directory
 *
 * @package       Bancha
 * @category      tests
 */
class AllNetworkTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('Network classes');

		$path = dirname(__FILE__) . DS . 'Network';
		$suite->addTestFile($path . DS . 'BanchaRequestCollectionTest.php');
		$suite->addTestFile($path . DS . 'BanchaRequestTransformerTest.php');
		$suite->addTestFile($path . DS . 'BanchaResponseCollectionTest.php');
		$suite->addTestFile($path . DS . 'BanchaResponseTransformerTest.php');

		$suite->addTestDirectory($path);
		return $suite;
	}
}
