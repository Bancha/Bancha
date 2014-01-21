<?php
/**
 * BanchExceptionsTest file.
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

	protected $_originalDebugLevel;

/**
 * tearDown setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->_originalDebugLevel = Configure::read('debug');

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

		// reset the debug level
		Configure::write('debug', $this->_originalDebugLevel);

		// enable stderr stream after testing (CakePHP 2.2 and up)
		if (in_array('stderr', CakeLog::configured()) && version_compare(Configure::version(), '2.2') >= 0) {
			CakeLog::enable('stderr');
		}
	}

/**
 * Tests exception handling with debug mode 2.
 *
 * @return void
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
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'throwExceptionMethod', // all execption details should be hidden
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(
					'title'			=> 'Hello World',
					'body'			=> 'foobar',
					'published'		=> false,
					'user_id'		=> 1,
				),
			),
			array(
				'action'		=> 'ArticlesException',
				'method'		=> 'throwNotFoundExceptionMethod', // this explicitly should send the type
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(),
			)
		));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$defaultPassExceptions = Configure::read('Bancha.passExceptions');
		Configure::write('Bancha.passExceptions', array('NotFoundException'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertCount(2, $responses);

		// show that there was an exception, but with no information!
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertFalse(isset($responses[0]->exceptionType)); // don't send exception info
		$this->assertEquals(__('Unknown error.', true), $responses[0]->message); // don't give usefull info to possible hackers
		$this->assertFalse(isset($responses[0]->where));
		$this->assertFalse(isset($responses[0]->trace));

		// show that there was an exception, but with no information!
		$this->assertEquals('exception', $responses[1]->type);
		$this->assertTrue(isset($responses[1]->exceptionType)); // send exception info
		$this->assertEquals(__('Invalid article', true), $responses[1]->message); // send exception message
		$this->assertFalse(isset($responses[1]->where)); // don't give usefull info to possible hackers
		$this->assertFalse(isset($responses[1]->trace));

		// tear down
		Configure::write('Bancha.passExceptions', $defaultPassExceptions);
	}

/**
 * That that different exceptions are catched correctly and
 * also the response contains the correct exceptions.
 *
 * @return void
 */
	public function testExceptionDebugModeExceptions() {
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
		$this->assertEquals(
			'In file "' . __FILE__ . '" on line ' . $GLOBALS['EXCEPTION_LINE'] . '.',
			$responses[2]->where
		);
	}

/**
 * Tests the exception logging.
 *
 * @return void
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
				'data'			=> array('param1', 'param2'),
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
		$this->assertRegExp(
			'/^2[0-9]{3}-[0-9]+-[0-9]+ [0-9]+:[0-9]+:[0-9]+ Error: A Bancha request to ' .
			'ThisControllerDoesNotExists::index\(\'param1\', \'param2\'\)' . // signature
			' resulted in the following MissingControllerException:/',
			$result
		);
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
 * @param string $id ignored
 * @return void
 * @throws Exception Always
 */
	public function throwExceptionMethod($id = null) {
		// we store the exception line to test it later.
		$GLOBALS['EXCEPTION_LINE'] = __LINE__ + 1; // exception if trigger directly below
		throw new Exception('Method specific error message, see bottom of this test');
	}

/**
 * Throws a exception for testing purposes.
 * 
 * @param string $id will be ignored
 * @return void
 * @throws NotFoundException always
 */
	public function throwNotFoundExceptionMethod($id = null) {
		throw new NotFoundException('Invalid article');
	}
}

