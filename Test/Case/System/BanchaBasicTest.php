<?php
/**
 * BanchaCrudTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('BanchaPaginatorComponent', 'Bancha.Controller/Component');

// TODO: refactor to use real test models.
require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaCrudTest
 *
 * All these tests are using the full stack of CakePHP components, not only testing
 * the functionallity of Bancha, but also that it is compatible to the current
 * CakePHP library (since bancha is using some internal methods)
 *
 * @package       Bancha.Test.Case.System
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class BanchaBasicTest extends CakeTestCase {

	public $fixtures = array('plugin.bancha.article');

	protected $_originalDebugLevel;

	protected $_originalIsPro;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->_originalDebugLevel = Configure::read('debug');
		$this->_originalIsPro = Configure::read('Bancha.isPro');

		// disable/drop stderr stream, to hide test's intentional errors in console and Travis
		// first check if stream exists, because if run from the browser it doesn't
		if (in_array('stderr', CakeLog::configured())) {
			if (version_compare(Configure::version(), '2.2') >= 0) {
				CakeLog::disable('stderr');
			} else {
				// just drop stderr for CakePHP 2.1 and older
				CakeLog::drop('stderr');
			}
		}
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		// reset the Bancha type
		Configure::write('Bancha.isPro', $this->_originalIsPro);

		// reset the debug level
		Configure::write('debug', $this->_originalDebugLevel);

		// clear the registry
		ClassRegistry::flush();

		// enable stderr stream after testing (CakePHP 2.2 and up)
		if (in_array('stderr', CakeLog::configured()) && version_compare(Configure::version(), '2.2') >= 0) {
			CakeLog::enable('stderr');
		}
	}

/**
 * test pagination
 *
 * @return void
 */
	public function testPaginationPage1() {
		Configure::write('Bancha.isPro', false);

		// Bancha Basic can load the first page
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 1,
				'limit'			=> 2,
			)),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check data

		// only first and second element should be loaded
		$this->assertEquals(2, count($responses[0]->result->data));
		$this->assertEquals(1001, $responses[0]->result->data[0]->id);
		$this->assertEquals(1002, $responses[0]->result->data[1]->id);
	}

/**
 * test pagination
 *
 * @return void
 */
	public function testPaginationPage2() {
		Configure::write('Bancha.isPro', false);

		// Bancha Basic can NOT load the second page
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 2,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 2,
				'limit'			=> 100,
			)),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// expect an exception message
		Configure::write('debug', 0);
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertEquals('exception', $responses[0]->type);

		// in debug expect an exception message
		Configure::write('debug', 2);
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertContains('Bancha Basic does not support pagiantion.', $responses[0]->message);
	}

/**
 * test pagination
 *
 * @return void
 */
	public function testPaginationRemoteFiltering() {
		Configure::write('Bancha.isPro', false);

		// Bancha Basic can NOT filter
		$rawPostData = json_encode(array(array(
			'action'		=> 'Article',
			'method'		=> 'read',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(array(
				'page'			=> 1,
				'limit'			=> 2,
				'filter'		=> array(array(
					'property'	=> 'user_id',
					'value'		=> 1
				))
			)),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// expect an exception message
		Configure::write('debug', 0);
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertEquals('exception', $responses[0]->type);

		// in debug expect an exception message
		Configure::write('debug', 2);
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertContains('Bancha Basic does not support remote filtering of data.', $responses[0]->message);
	}

/**
 * test pagination
 *
 * @return void
 * @expectedException         BanchaException
 * @expectedExceptionMessage  Bancha Basic does not support remote filtering of data,
 */
	public function testPaginationAllowedFilters() {
		Configure::write('Bancha.isPro', false);

		// Setting allowed filters should result in an an exception

		// setup the paginator component
		$banchaPaginatorComponent = new BanchaPaginatorComponent(new ComponentCollection(), array());

		// here is should break
		$banchaPaginatorComponent->setAllowedFilters(array('id'));
	}
}
