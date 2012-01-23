<?php
/**
 * BanchExceptionsTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaExceptionsTest. Tests if the Exception was thrown, the correct controller was choosen.
 *
 * @package       Bancha
 * @category      Tests
 */
class BanchaExceptionsTest extends CakeTestCase {
	private $standardDebugLevel;
	
	
	public function setUp() {
		 $this->standardDebugLevel = Configure::read('debug');
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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));

		// set debug level back to normal
		Configure::write('debug', $this->standardDebugLevel);
		
		// check exception
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('Exception', $responses[0]->exceptionType); // this is the class anme, see bottom
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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		// set debug level back to normal
		Configure::write('debug', $this->standardDebugLevel);
		
		// show that there was an exception, but with no information!
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertFalse(isset($responses[0]->exceptionType)); // don't send exception info
		$this->assertEquals(__('Unknown error.',true),$responses[0]->message); // don't give usefull info to possible hackers
	}

/**
 * Tests the exception handling.
 *
 */
	public function testExceptions() {
		
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

		// Create dispatcher and dispatch requests.
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
	
	
		// set debug level back to normal
		Configure::write('debug', $this->standardDebugLevel);
	
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

