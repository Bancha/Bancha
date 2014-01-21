<?php
/**
 * TreeIntegrationTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package 	Bancha.Test.Case.System
 * @copyright	Copyright 2011-2013 codeQ e.U.
 * @link		http://banchaproject.org Bancha Project
 * @since		Bancha v 0.9.0
 * @author		Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author		Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('CakeResponse', 'Network');
App::uses('CategoriesController', 'Controller');

/**
 * TreeIntegrationTest
 *
 * All these tests are using the full stack of CakePHP components, not only testing
 * the functionallity of Bancha, but also that it is compatible to the current
 * CakePHP library (since Bancha is using some internal methods)
 *
 * @package 	Bancha.Test.Case.System
 * @author		Roland Schuetz <mail@rolandschuetz.at>
 * @since		Bancha v 2.2.0
 */
class TreeIntegrationTest extends CakeTestCase {

	public $fixtures = array('plugin.bancha.category');

/**
 * Keeps a reference to the default paths, since
 * we need to change them in the setUp method
 * @var Array
 */
	protected $_originalPaths = null;

	protected $_originalDebugLevel;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->_originalDebugLevel = Configure::read('debug');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->_originalDebugLevel);

		// clear the registry
		ClassRegistry::flush();
	}

/**
 * Test that a controller with a threaded find returns the data 
 * like Sencha Touch/Ext JS expects them.
 * 
 * @return void
 */
	public function testIndex() {
		// build up the test paths
		$this->_originalPaths = App::paths();
		App::build(array(
			'Controller' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Controller' . DS,
			'Model' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Model' . DS,
		), App::RESET);

		// Build a Sencha request
		$rawPostData = json_encode(array(array(
			'action'		=> 'Category',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
			)),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$controller = new CategoriesController();
		$controller->Category = ClassRegistry::init('Category');

		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)), true);
		$this->assertTrue(isset($responses[0]['result']), 'Expected an result for request, instead $responses is ' . print_r($responses, true));

		// basic tree checks
		$this->assertEquals(1, count($responses[0]['result']['data']), 'Expected one root element.');
		$this->assertEquals('My Categories', $responses[0]['result']['data'][0]['name'], 'Expected one root element with name "My Categories".');
		$this->assertTrue(isset($responses[0]['result']['data'][0]['data']), 'Expected that the root element has children.');

		// Fixture test data originally comes from
		// http://book.cakephp.org/2.0/en/core-libraries/behaviors/tree.html#basic-usage
		// this is the expected formatting for Sencha Touch/Ext JS
		$expected = array(
			array(
				'name' => 'My Categories',
				'id' => 1,
				'parent_id' => null,
				'lft' => 1,
				'rght' => 30,
				'data' => array(
					array(
						'name' => 'Fun',
						'id' => 2,
						'parent_id' => 1,
						'lft' => 2,
						'rght' => 15,
						'data' => array(
							array(
								'name' => 'Sport',
								'id' => 3,
								'parent_id' => 2,
								'lft' => 3,
								'rght' => 8,
								'data' => array(
									array(
										'name' => 'Surfing',
										'id' => 4,
										'parent_id' => 3,
										'lft' => 4,
										'rght' => 5,
										'leaf' => true
									),
									array(
										'name' => 'Extreme knitting',
										'id' => 5,
										'parent_id' => 3,
										'lft' => 6,
										'rght' => 7,
										'leaf' => true
									)
								)
							),
							array(
								'name' => 'Friends',
								'id' => 6,
								'parent_id' => 2,
								'lft' => 9,
								'rght' => 14,
								'data' => array(
									array(
										'name' => 'Gerald',
										'id' => 7,
										'parent_id' => 6,
										'lft' => 10,
										'rght' => 11,
										'leaf' => true
									),
									array(
										'name' => 'Gwendolyn',
										'id' => 8,
										'parent_id' => 6,
										'lft' => 12,
										'rght' => 13,
										'leaf' => true
									)
								)
							),
						)
					),
					array(
						'name' => 'Work',
						'id' => 9,
						'parent_id' => 1,
						'lft' => 16,
						'rght' => 29,
						'data' => array(
							array(
								'name' => 'Reports',
								'id' => 10,
								'parent_id' => 9,
								'lft' => 17,
								'rght' => 22,
								'data' => array(
									array(
										'name' => 'Annual',
										'id' => 11,
										'parent_id' => 10,
										'lft' => 18,
										'rght' => 19,
										'leaf' => true
									),
									array(
										'name' => 'Status',
										'id' => 12,
										'parent_id' => 10,
										'lft' => 20,
										'rght' => 21,
										'leaf' => true
									)
								)
							),
							array(
								'name' => 'Trips',
								'id' => 13,
								'parent_id' => 9,
								'lft' => 23,
								'rght' => 28,
								'data' => array(
									array(
										'name' => 'National',
										'id' => 14,
										'parent_id' => 13,
										'lft' => 24,
										'rght' => 25,
										'leaf' => true
									),
									array(
										'name' => 'International',
										'id' => 15,
										'parent_id' => 13,
										'lft' => 26,
										'rght' => 27,
										'leaf' => true
									)
								)
							),
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $responses[0]['result']['data']);

		// reset the paths
		App::build($this->_originalPaths, App::RESET);
	}
}
