<?php
/**
 * BanchDebugExceptionsTest file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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
	}

/**
 * Helper method to fake an request
 *
 * @param string $method The method to execute on the DebugException controller
 * @param array  $data   The data to pass
 * @return array $responses
 */
	protected function _getResultForMethod($method, $data = array()) {
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
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		return $responses;
	}

/**
 * When a controller method doesn't return anything, throw an exception.
 * This is implemented in BanchaResponseTransformer::transform()
 *
 * @return void
 */
	public function testNoMethodResultException() {
		$responses = $this->_getResultForMethod('getNoResult');

		// check exception
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('BanchaException', $responses[0]->exceptionType);
	}

/**
 * Currently is is not expected that cakes gets multiple records from ext in one request.
 * If this is happening tell the developer that he probably did an error.
 * This exception is trown in BanchaRequestTransformer::transformDataStructureToCake()
 *
 * @return void
 * @expectedException BanchaException
 */
	public function testMultipleRecordInputException() {
		$this->_getResultForMethod('returnTrue', array(array(
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
 *
 * @return void
 */
	public function testDeactivatedMultipleRecordInputException() {
		// the developer can deactivate this by setting this config
		Configure::write('Bancha.allowMultiRecordRequests', true);

		// we expect no exception
		$this->_getResultForMethod('returnTrue', array(array(
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
 * 
 * @return void
 */
	public function getNoResult() {
	}

/**
 * simple test function
 * 
 * @return boolean true
 */
	public function returnTrue() {
		return true;
	}
}
