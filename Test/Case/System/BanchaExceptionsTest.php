<?php
/**
 * BanchExceptionsTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaExceptionsTest. Tests if the Exception was thrown, the correct controller was choosen.
 *
 * @package       Bancha.Test.Case.System
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 * @since         Bancha v 0.9.0
 */
class BanchaExceptionsTest extends CakeTestCase {


	private $originalOrigin;
	private $originalDebugLevel;

	public function setUp() {
		parent::setUp();

		$this->originalDebugLevel = Configure::read('debug');
	}

	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->originalDebugLevel);
	}

/**
 * Tests exception handling with debug mode 2.
 *
 */
	public function testExceptionDebugMode() {

		Configure::write('debug', 2);

		$rawPostData = json_encode(array(
			'action'		=> 'ArticlesException',
			'method'		=> 'throwExceptionMethod',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check exception
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('Exception', $responses[0]->exceptionType); // this is the class name, see bottom
	}

/**
 * Tests exception handling with debug mode 0.
 *
 * @return void
 * @author Florian Eckerstorfer
 */
	public function testExceptionProductionMode() {

		Configure::write('debug', 0);

		$rawPostData = json_encode(array(
			'action'		=> 'ArticlesException',
			'method'		=> 'throwExceptionMethod',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(
				'title'			=> 'Hello World',
				'body'			=> 'foobar',
				'published'		=> false,
				'user_id'		=> 1,
			),
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// show that there was an exception, but with no information!
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertFalse(isset($responses[0]->exceptionType)); // don't send exception info
		$this->assertEquals(__('Unknown error.',true),$responses[0]->message); // don't give usefull info to possible hackers
	}

/**
 * That that different exceptions are catched correctly and
 * also the response contains the correct exceptions.
 */
	public function testExceptionDebugMode_Exceptions() {

		Configure::write('debug', 2);

		// Create some requests.
		$rawPostData = json_encode(array(
			array(
				'action'		=> 'ThisControllerDoesNotExist',
				'method'		=> 'index',
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(),
			),
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'throwNotFoundExceptionMethod',
				'tid'			=> 2,
				'type'			=> 'rpc',
				'data'			=> array(),
			),
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'throwExceptionMethod',
				'tid'			=> 3,
				'type'			=> 'rpc',
				'data'			=> array(),
			),
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// verity
		$this->assertEquals(3, count($responses), 'Three requests result in three responses.');

		// controller class not found
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('MissingControllerException', $responses[0]->exceptionType);
		$this->assertEquals('Controller class ThisControllerDoesNotExistsController could not be found.', $responses[0]->message);
		$this->assertTrue(!empty($responses[0]->where), 'message');

		// application controller method exception catched
		$this->assertEquals('exception', $responses[1]->type);
		$this->assertEquals('NotFoundException', $responses[1]->exceptionType);
		$this->assertEquals('Invalid article', $responses[1]->message);

		// another application level error
		$this->assertEquals('exception', $responses[2]->type);
		$this->assertEquals('Exception', $responses[2]->exceptionType);
		$this->assertEquals('Method specific error message, see bottom of this test', $responses[2]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $GLOBALS['EXCEPTION_LINE'] . '.',
				$responses[2]->where, 'message');
	}


/**
 * Tests the exception logging.
 *
 */
	public function testExceptionLogging() {
		$originalLogExceptions = Configure::read('Bancha.logExceptions');

		// this should log exceptions
		Configure::write('debug', 0);
		Configure::write('Bancha.logExceptions', true);

		// delete the log file
		if (file_exists(LOGS . 'error.log')) {
			unlink(LOGS . 'error.log');
		}

		// Create some requests.
		$rawPostData = json_encode(array(
			array(
				'action'		=> 'ThisControllerDoesNotExist',
				'method'		=> 'index',
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array('param1','param2'),
			)
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// Expect a missing conroller exception in the result
		// In Production mode the exact type is not send, only the Ext.Direct type
		$this->assertEquals('exception', $responses[0]->type);

		// Expect a missing conroller exception in the logs
		$this->assertTrue(file_exists(LOGS . 'error.log'));
		$result = file_get_contents(LOGS . 'error.log');
		$this->assertRegExp('/^2[0-9]{3}-[0-9]+-[0-9]+ [0-9]+:[0-9]+:[0-9]+ Error: A Bancha request to '.
							'ThisControllerDoesNotExists::index\(\'param1\', \'param2\'\)'. // signature
							' resulted in the following MissingControllerException:/', $result);
		unlink(LOGS . 'error.log');

		// this should not log
		Configure::write('Bancha.logExceptions', false);
		Configure::write('debug', 2);

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// Expect a missing conroller exception in the result
		// In Production mode the exact type is not send, only the Ext.Direct type
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertFalse(file_exists(LOGS . 'error.log'));


		// In Debug Mode we want to see no error log
		Configure::write('Bancha.logExceptions', true);
		Configure::write('debug', 2);

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// Expect a missing conroller exception in the result
		// In Production mode the exact type is not send, only the Ext.Direct type
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertFalse(file_exists(LOGS . 'error.log'));

		// tear down
		Configure::write('Bancha.logExceptions', $originalLogExceptions);
	}
}

/**
 * Articles Controller, uses view method to throw an exception
 *
 * @package       Bancha
 * @category      TestFixtures
 */
class ArticlesExceptionsController extends ArticlesController {

/**
 * throwExceptionMethod method
 *
 * @param string $id
 * @return void
 */
	public function throwExceptionMethod($id = null) {
		// we store the current line to test it later.
		$GLOBALS['EXCEPTION_LINE'] = __LINE__; throw new Exception('Method specific error message, see bottom of this test');
	}
	public function throwNotFoundExceptionMethod($id = null) {
		throw new NotFoundException('Invalid article');
	}
}

