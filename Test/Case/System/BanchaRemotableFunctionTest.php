<?php
/**
 * BanchaRemotableFunctionTest file.
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
App::uses('AuthComponent', 'Controller/Component');
App::uses('Controller', 'Controller');

/**
 * Provides some controller functions to use for system tests.
 *
 * @package       Bancha.Test.Case.System
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class RemotableFunctionsController extends Controller {

/**
 * Use the BanchaPaginatorComponent to also support pagination
 * and remote searching for Sencha Touch and Ext JS stores
 */
	public $components = array('Session', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));

/**
 * Return the controller method arguments to check if Bancha sets them correctly
 * 
 * @param string $param1 Arbitrary content which is returned
 * @param string $param2 Arbitrary content which is returned
 * @return array         Array of params
 */
	public function returnInputParameters($param1, $param2) {
		return array($param1, $param2);
	}

/**
 * Return the request data to check if Bancha sets them correctly
 * 
 * @param string $param1 Arbitrary content
 * @param string $param2 Arbitrary content
 * @return array         The request data
 */
	public function returnInputRequestData($param1, $param2) {
		if (is_null($this->request->data)) {
			return false; // this is a nicer error message for debugging what's going wrong here
		}
		return $this->request->data;
	}

/**
 * This method will redirect to a different url
 *
 * @return void
 */
	public function redirectMethod() {
		$this->redirect('redirected-page.html', 302);
	}

}

class RemotableFunctionWithRequestHandlersController extends RemotableFunctionsController {

/**
 * Add the RequestHandler here to test compability
 */
	public $components = array('Session', 'RequestHandler', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));
}
class RemotableFunctionWithAuthComponentsController extends RemotableFunctionsController {

/**
 * Add the RequestHandler here to test compability
 */
	public $components = array(
		'Session',
		'Auth' => array(
			// this config would normally lead to problemy, test that BanchaPagiantorComponent fixes the problem
			'ajaxLogin' => '/users/session_expired',
			// make sure we can check for authorizations as well
			'authorize' => 'Controller'
		),
		'Paginator' => array('className' => 'Bancha.BanchaPaginator')
	);

/**
 * Returns true is the method is not prohibitMethod
 * 
 * @param array $user Not used
 * @return boolean Returns true if request action is not method isAuthorized
 */
	public function isAuthorized($user = null) {
		return $this->request->action !== 'prohibitMethod';
	}

/**
 * A fake prohibited method for testing
 * 
 * @return void
 */
	public function prohibitMethod() {
	}

}

/**
 * BanchaRemotableFunctionTest
 *
 * All these tests are using the full stack of CakePHP components, not only testing
 * the functionallity of Bancha, but also that it is compatible to the current
 * CakePHP library (since Bancha is using some internal methods)
 *
 * @package       Bancha
 * @category      Tests
 */
class BanchaRemotableFunctionTest extends CakeTestCase {

	protected $_originalOrigin;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		// Bancha will check that this is set, so for all tests which are not
		// about the feature, this should be set.
		$_SERVER['HTTP_ORIGIN'] = 'http://example.org';
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		// reset the origin
		if ($this->_originalOrigin !== false) {
			$_SERVER['HTTP_ORIGIN'] = $this->_originalOrigin;
		} else {
			unset($_SERVER['HTTP_ORIGIN']);
		}

		// clean the registry
		ClassRegistry::flush();
	}

/**
 * Test setting of the input parameters
 *
 * @return void
 */
	public function testInputParametersAndReturnTransformation() {
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputParameters',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is ' . print_r($responses, true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunction', $responses[0]->action);
		$this->assertEquals('returnInputParameters', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}

/**
 * Test setting of the request data
 *
 * @return void
 */
	public function testRequestDataAndReturnTransformation() {
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is ' . print_r($responses, true));
		$this->assertTrue(isset($responses[0]->result->data), 'Expected that $this->request->data is not null.');
		$this->assertTrue(is_array($responses[0]->result->data), 'Expected the data-result to be an array, instead got ' . print_r($responses, true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunction', $responses[0]->action);
		$this->assertEquals('returnInputRequestData', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}

/**
 * Without any adoption the RequestHandler would break Bancha's definition
 * of the $request->data.
 *
 * This is fixed in the BanchaPaginatorComponent::initialize.
 *
 * Since it makes no sense to test this in a unit test, this provides an
 * integration test where we go though the Dispatcher to check if everything
 * works as expected.
 *
 * This test is exactly the same as the one above except that they are running
 * against a Controller with an activated RequestHandler.
 *
 * @return void
 */
	public function testRequestDataAndReturnTransformationWithRequestHandler() {
		// keep original values
		$httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : false;
		$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : false;

		// mimik the type json to trigger the RequestHandler
		$_SERVER['HTTP_ACCEPT'] = 'application/json';
		$_SERVER['CONTENT_TYPE'] = 'application/json';

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunctionWithRequestHandler',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertTrue(
			isset($responses[0]->result),
			'Expected an result for first request, instead $responses is ' . print_r($responses, true)
		);
		$this->assertTrue(
			isset($responses[0]->result->data),
			'Expected that $this->request->data is not null.'
		);
		$this->assertFalse(
			is_string($responses[0]->result->data),
			'Expected the data-result to be an array, instead got a string ' . var_export($responses[0]->result->data, true)
		);
		$this->assertTrue(
			is_array($responses[0]->result->data),
			'Expected the data-result to be an array, instead got ' . print_r($responses, true)
		);
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);

		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunctionWithRequestHandler', $responses[0]->action);
		$this->assertEquals('returnInputRequestData', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// tear down
		$_SERVER['HTTP_ACCEPT'] = $httpAccept;
		$_SERVER['CONTENT_TYPE'] = $contentType;
	}

/**
 * Returns an AuthComponent to log in and out.
 * 
 * @return AuthComponent
 */
	protected function _getAuthComponent() {
		// setup the component collection
		$Collection = new ComponentCollection();

		// setup the auth component
		$auth = new AuthComponent($Collection, array());

		return $auth;
	}

/**
 * The redirect method does not make sense when using Bancha. Therefore
 * if a redirect happens we want to throw an exception.
 *
 * @return void
 */
	public function testRedirectHandling() {
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'redirectMethod',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array(),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is ' . print_r($responses, true));
		$this->assertEquals('BanchaRedirectException', $responses[0]->exceptionType);
	}

/**
 * Without any adoption the AuthComponent would render an element if the config
 * ajaxLogin is set and the user is not logged in.
 *
 * This is fixed in the BanchaPaginatorComponent::initialize.
 *
 * Since it makes no sense to test this in a unit test, this provides an
 * integration test where we go though the Dispatcher to check if everything
 * works as expected.
 *
 * @return void
 */
	public function testAuthComponentNotLoggedIn() {
		// make sure we are logged out
		$this->_getAuthComponent()->logout();

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunctionWithAuthComponent',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is ' . print_r($responses, true));
		$this->assertEquals('BanchaAuthLoginException', $responses[0]->exceptionType);
	}

/**
 * Without any adoption the AuthComponent would trigger a redirect if the user
 * is not authorized to use this method.
 *
 * Since it makes no sense to test this in a unit test, this provides an
 * integration test where we go though the Dispatcher to check if everything
 * works as expected.
 *
 * @return void
 */
	public function testAuthComponentNotAuthorized() {
		// log in
		$this->_getAuthComponent()->login(array('name' => 'LoggedIn'));

		// ******************************************************
		//       Test that allowed method works as normal
		// ******************************************************

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunctionWithAuthComponent',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertTrue(
			isset($responses[0]->result),
			'Expected an result for first request, instead $responses is ' . print_r($responses, true)
		);
		$this->assertTrue(
			isset($responses[0]->result->data),
			'Expected that $this->request->data is not null.'
		);
		$this->assertFalse(
			is_string($responses[0]->result->data),
			'Expected the data-result to be an array, instead got a string ' . var_export($responses[0]->result->data, true)
		);
		$this->assertTrue(
			is_array($responses[0]->result->data),
			'Expected the data-result to be an array, instead got ' . print_r($responses, true)
		);
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);

		// ******************************************************
		//   Test that denied method returns a proper exception
		// ******************************************************

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunctionWithAuthComponent',
			'method'		=> 'prohibitMethod',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));

		// setup
		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is ' . print_r($responses, true));
		$this->assertEquals('BanchaAuthAccessRightsException', $responses[0]->exceptionType);

		// tear down
		$this->_getAuthComponent()->logout();
	}
}
