<?php
/**
 * BanchDebugExceptionsTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaDebugExceptionsTest. Tests if the Exception to help the user develop are thrown correctly
 *
 * @package       Bancha.Test.Case.System
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class BanchaDebugExceptionsTest extends CakeTestCase {

	private $originalOrigin;
	private $originalDebugLevel;

	public function setUp() {
		parent::setUp();

		$this->originalOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : false;
		$this->originalDebugLevel = Configure::read('debug');

		// Bancha will check that this is set, so for all tests which are not
		// about the feature, this should be set.
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';
	}

	public function tearDown() {
		parent::tearDown();

		// reset the origin
		if($this->originalOrigin !== false) {
			$_SERVER['HTTP_ORIGIN'] = $this->originalOrigin;
		} else {
			unset($_SERVER['HTTP_ORIGIN']);
		}

		// reset the debug level
		Configure::write('debug', $this->originalDebugLevel);
	}

	// helper method
	private function getResultForMethod($method,$data=array()) {
		$rawPostData = json_encode(array(
			'action'		=> 'DebugException',
			'method'		=> $method,
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> $data,
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to net set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		return $responses;
	}

/**
 * When a controller method doesn't return anything, throw an exception.
 * This is implemented in BanchaResponseTransformer::transform()
 */
	public function testNoMethodResultException() {

		$responses = $this->getResultForMethod('getNoResult');

		// check exception
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('BanchaException', $responses[0]->exceptionType);
	}

/**
 * Currently is is not expected that cakes gets multiple records from ext in one request.
 * If this is happening tell the developer that he probably did an error.
 * This exception is trown in BanchaRequestTransformer::transformDataStructureToCake()
 *
 * @expectedException BanchaException
 */
	public function testMultipleRecordInputException() {
		$this->getResultForMethod('returnTrue', array(array(
			'data' => array(
				array( // first
					'id' => 1,
					'name' => 'foo',
				),
				array( // second
					'id' => 2,
					'name' => 'bar',
				),
			)
		)));
	}


/**
 * See testMultipleRecordInputException()
 * See also BanchaRequestTransformerTest::testTransformDataStructureToCake_MultipleRecords
 */
	public function testDeactivatedMultipleRecordInputException() {

		// the developer can deactivate this by setting this config
		Configure::write('Bancha.allowMultiRecordRequests',true);

		// we expect no exception
		$this->getResultForMethod('returnTrue', array(array(
			'data' => array(
				array( // first
					'id' => 1,
					'name' => 'foo',
				),
				array( // second
					'id' => 2,
					'name' => 'bar',
				),
			)
		)));
	}
}





/**
 * DebugExceptionsController, has many errors a developer can make
 *
 * @package       Bancha.Test.Case.System
 * @category      TestFixtures
 */
class DebugExceptionsController extends ArticlesController {

/**
 * User forgot to set a return value
 */
	public function getNoResult() {
	}

/**
 * simple test function
 */
	public function returnTrue() {
		return true;
	}
}

