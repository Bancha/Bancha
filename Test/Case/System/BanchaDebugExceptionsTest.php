<?php
/**
 * BanchExceptionsTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

App::uses('ArticlesController', 'Controller');

// require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaDebugExceptionsTest. Tests if the Exception to help the user develop are thrown correctly
 *
 * @package       Bancha
 * @category      Tests
 */
class BanchaDebugExceptionsTest extends CakeTestCase {
	
	
	// helper method
	private function getResultForMethod($method,$data=array()) {
		$rawPostData = json_encode(array(
			'action'		=> 'DebugException',
			'method'		=> $method,
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> $data,
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
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
		$this->assertEquals('CakeException', $responses[0]->exceptionType);
	}
	
/**
 * Currently is is not expected that cakes gets multiple records from ext in one request.
 * If this is happening tell the developer that he probably did an error.
 * This exception is trown in BanchaRequestTransformer::transformDataStructureToCake()
 *
 * @expectedException CakeException
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
 * @package       Bancha
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

