<?php
/**
 * BanchaDispatcherTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha.Test.Case.Routing
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('AppController', 'Controller');
App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * TestsController class
 *
 * @package       Bancha.Test.Case.Routing
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class TestsController extends AppController {

	public function testaction1() {
		return array('text' => 'Hello World!');
	}

	public function testaction2() {
		return array('text' => 'foobar');
	}

	public function returnTrue() {
		return true;
	}
}

/**
 * BanchaDispatcherTest
 *
 * @package       Bancha.Test.Case.Routing
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class BanchaDispatcherTest extends CakeTestCase {

	private $originalOrigin;
	private $originalDebugLevel;

	public function setUp() {
		parent::setUp();

		$this->originalOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : false;
		$this->originalDebugLevel = Configure::read('debug');
		$this->allowedDomains = Configure::read('Bancha.allowedDomains');
		
		// disable/drop stderr stream, to hide test's intentional errors in console and Travis
		if (version_compare(Configure::version(), '2.2') >= 0) {
			CakeLog::disable('stderr');
		} else {
			// just drop stderr for CakePHP 2.1 and older
			CakeLog::drop('stderr');
		}
	}

	public function tearDown() {
		parent::tearDown();

		// reset the origin
		if($this->originalOrigin !== false) {
			$_SERVER['HTTP_ORIGIN'] = $this->originalOrigin;
		} else {
			unset($_SERVER['HTTP_ORIGIN']);
		}

		// reset the debug level and allowed domains
		Configure::write('debug', $this->originalDebugLevel);
		Configure::write('Bancha.allowedDomains', $this->allowedDomains);

		// enable stderr stream after testing (CakePHP 2.2 and up)
		if (version_compare(Configure::version(), '2.2') >= 0) {
			CakeLog::enable('stderr');
		}
	}

/**
 * Tests the dispatch() method of BanchaDispatcher with the 'return'-option. Thus dispatch() doesn't send the response
 * to the browser but returns it instead. We are able to mock the BanchaRequest object, but we are not able to mock
 * the other objects used by the Dispatcher. Especially we need to provide an actual controller class. TestsController is
 * defined at the bottom of this file.
 *
 * This tests dispatches two actions and tests if the expected content is available in the combined response.
 *
 */
	public function testDispatchWithReturn() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'Test', // will be pluralized
				'method'	=> 'testaction1',
				'data'		=> array(),
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction2',
				'data'		=> array(),
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertTrue(isset($responses[0]->result), 'Expected $responses[0]->result to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Hello World!', $responses[0]->result->data->text);
		$this->assertEquals('foobar', $responses[1]->result->data->text);
	}

/**
 * Tests the dispatch() method of BanchaDispatcher without the 'return'-option. Thus dispatch() sends the response
 * directly to the browser. We need to capture the output to test it.
 *
 */
	public function testDispatchWithResponseSend() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'testaction2',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$responses = json_decode(ob_get_clean());

		// verify
		$this->assertTrue(isset($responses[0]->result), 'Expected $responses[0]->result to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('Hello World!', $responses[0]->result->data->text);
		$this->assertEquals('foobar', $responses[1]->result->data->text);
	}

/**
 * Bancha should not throw PHP Exceptions, because Sencha can't handle this,
 * instead it should send Ext.Direct exceptions
 *
 * @return void
 */
	public function testMissingController_Debug() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'SomeController',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// should display the error type and message
		Configure::write('debug', 2);

		// this should "throw" a Sencha exception
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertTrue(isset($responses[0]->type), 'Expected $responses[0]->type to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('MissingControllerException', $responses[0]->exceptionType);
		$this->assertEquals('Controller class SomeControllersController could not be found.', $responses[0]->message);
	}


/**
 * Bancha should not throw PHP Exceptions, because Sencha can't handle this,
 * instead it should send Ext.Direct exceptions
 *
 * @return void
 */
	public function testMissingController_Production() {

		// input
		$rawPostData = json_encode(array(
			array(
				'action'	=> 'SomeController',
				'method'	=> 'testaction1',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			)
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// should display the error type and message
		Configure::write('debug', 0);

		// this should "throw" a Sencha exception
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// verify
		$this->assertTrue(isset($responses[0]->type), 'Expected $responses[0]->type to pre present, instead $responses is '.print_r($responses,true));
		$this->assertEquals('exception', $responses[0]->type);

		// this data should be protected
		$this->assertFalse(isset($responses[0]->exceptionType));
		$this->assertEquals('Unknown error.', $responses[0]->message);
	}



/**
 * Tests that Bancha only requires an HTTP_ORIGIN header when Bancha.allowedDomains is set
 * (Mainly for CORS support)
 */
	public function testRequireHttpOriginHeader_Pass() {

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// expect it to still work
		unset($_SERVER['HTTP_ORIGIN']);

		// the origin is set in the setup, check that we pass
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));

		// check success
		$this->assertTrue($responses[0]->result->success);
	}

/**
 * Tests that Bancha only requires an HTTP_ORIGIN header, if Bancha.allowedDomains is set
 * (Mainly for CORS support)
 */
	public function testRequireHttpOriginHeader_Rejected_Debug() {

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// now expect it to be rejected
		unset($_SERVER['HTTP_ORIGIN']);

		// expect a debug message
		Configure::write('debug', 2);
		Configure::write('Bancha.allowedDomains', array('http://example.org'));

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$rawResponse = ob_get_clean();

		// check error message
		$this->assertEqual('Bancha Error: Bancha expects that any request has a HTTP_ORIGIN header.', $rawResponse);
	}

/**
 * Tests that Bancha only requires an HTTP_ORIGIN header, if Bancha.allowedDomains is set
 * (Mainly for CORS support)
 */
	public function testRequireHttpOriginHeader_Rejected_Production() {

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// now expect it to be rejected
		unset($_SERVER['HTTP_ORIGIN']);

		// expect no debug message
		Configure::write('debug', 0);
		Configure::write('Bancha.allowedDomains', array('http://example.org'));

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$rawResponse = ob_get_clean();

		// check error message
		$this->assertEqual('', $rawResponse);
	}

/**
 * Tests that Bancha checks Bancha.allowedDomains
 * (CORS support)
 */
	public function testAllowedDomainsRestriction_Accepted() {

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// set the test domain
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';

		// test wildcard
		Configure::write('Bancha.allowedDomains', '*');
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertTrue($responses[0]->result->success);

		// test domain match
		Configure::write('Bancha.allowedDomains', array(
			'http://example.org',
			'http://another-example.org'
		));
		$responses = json_decode($Dispatcher->dispatch($collection, $response, array('return' => true)));
		$this->assertTrue($responses[0]->result->success);
	}

/**
 * Tests that Bancha checks Bancha.allowedDomains
 */
	public function testAllowedDomainsRestriction_Rejected_Debug() {

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// set the test domain
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';

		// domain is not supported
		Configure::write('Bancha.allowedDomains', array(
			'http://another-example.org'
		));

		// Show debug message
		Configure::write('debug', 2);

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$rawResponse = ob_get_clean();

		// check error message
		$this->assertEqual('Bancha Error: According to the Configure::read("Bancha.allowedDomains") this request is not allowed!', $rawResponse);
	}

/**
 * Tests that Bancha handles preflight requests (request type OPTIONS)
 */
	public function testOptionResponses_Rejected_Debug() {
		$originalRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// set the test domain
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';

		// Show debug message
		Configure::write('debug', 2);

		// domain is not supported
		Configure::write('Bancha.allowedDomains', array(
			'http://another-example.org'
		));

		// fake preflight request
		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';

		// capture output, because we want to test that the content is send
		// see also CakePHP DispatcherTest::testDispatchActionReturnsResponse
		ob_start();
		$Dispatcher->dispatch($collection, $response);
		$rawResponse = ob_get_clean();

		// check error message
		$this->assertEqual('Bancha Error: According to the Configure::read("Bancha.allowedDomains") this request is not allowed!', $rawResponse);

		// tear down
		if($originalRequestMethod !== false ) {
			$_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
		} else {
			unset($_SERVER['REQUEST_METHOD']);
		}
	}

/**
 * Tests that Bancha handles preflight requests (request type OPTIONS)
 */
	public function testOptionResponses_Pass() {
		$originalRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// set the test domain
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';

		// Show debug message
		Configure::write('debug', 2);

		// domain is allowed
		Configure::write('Bancha.allowedDomains', array(
			'http://example.org'
		));

		// fake preflight request
		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';

		// test
		$rawResponse = $Dispatcher->dispatch($collection, $response, array('return' => true));

		// expect no content
		$this->assertTrue(empty($rawResponse));

		// expect the CORS headers to be set
		$headers = $response->header();
		$this->assertEqual('POST, OPTIONS', $headers['Access-Control-Allow-Methods']);
		$this->assertEqual('Origin, X-Requested-With, Content-Type', $headers['Access-Control-Allow-Headers']);
		$this->assertEqual('http://example.org', $headers['Access-Control-Allow-Origin']);
		$this->assertEqual('3600', $headers['Access-Control-Max-Age']);


		// tear down
		if($originalRequestMethod !== false) {
			$_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
		} else {
			unset($_SERVER['REQUEST_METHOD']);
		}
	}

/**
 * Tests that Bancha sets the Access-Control-Allow-Origin to star, if Bancha.allowedDomains is set to star
 * This is currently important, because the CORS doesn't allow Access-Control-Allow-Origin to be a list of
 * entries and legacy CakePHP versions don't allow multiple Access-Control-Allow-Origin to be set.
 *
 * See also:
 * https://cakephp.lighthouseapp.com/projects/42648-cakephp/tickets/3960-cakeresponseheader-and
 */
	public function testOptionResponses_Pass_AllDomains() {
		$originalRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

		// input
		$rawPostData = json_encode(array(
			'action'		=> 'Test',
			'method'		=> 'returnTrue',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		));

		// setup
		$collection = new BanchaRequestCollection($rawPostData);
		$Dispatcher = new BanchaDispatcher();
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// set the test domain
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';

		// Show debug message
		Configure::write('debug', 2);

		// all domains are allowed
		Configure::write('Bancha.allowedDomains', '*');

		// fake preflight request
		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';

		// test
		$rawResponse = $Dispatcher->dispatch($collection, $response, array('return' => true));

		// expect no content
		$this->assertTrue(empty($rawResponse));

		// expect the CORS headers to be set
		$headers = $response->header();
		$this->assertEqual('POST, OPTIONS', $headers['Access-Control-Allow-Methods']);
		$this->assertEqual('Origin, X-Requested-With, Content-Type', $headers['Access-Control-Allow-Headers']);
		$this->assertEqual('*', $headers['Access-Control-Allow-Origin']);
		$this->assertEqual('3600', $headers['Access-Control-Max-Age']);


		// tear down
		if($originalRequestMethod !== false) {
			$_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
		} else {
			unset($_SERVER['REQUEST_METHOD']);
		}
	}
}
