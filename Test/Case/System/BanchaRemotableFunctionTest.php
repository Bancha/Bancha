<?php
/**
 * BanchaRemotableFunctionTest file.
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
	 * and remote searching for Sencha Touch and ExtJS stores
	 */
	public $components = array('Session', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));
	/**
	 * Return the controller method arguments to check if Bancha sets them correctly
	 */
	public function returnInputParameters($param1, $param2) {
		return array($param1, $param2);
	}
	/**
	 * Return the request data to check if Bancha sets them correctly
	 */
	public function returnInputRequestData($param1, $param2) {
		if(is_null($this->request->data)) {
			return false; // this is a nicer error message for debugging what's going wrong here
		}
		return $this->request->data;
	}
	/**
	 * This method will redirect to a different url
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
	public $components = array('Session', 'Auth' => array(
		// this config would normally lead to problemy, test that BanchaPagiantorComponent fixes the problem
		'ajaxLogin' => '/users/session_expired',
		// make sure we can check for authorizations as well
		'authorize' => 'Controller'
	), 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));

	public function isAuthorized($user = null) {
		return $this->request->action !== 'prohibitMethod';
	}

	public function prohibitMethod() {}
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
	public function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}

	public function testInputParametersAndReturnTransformation() {
		
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputParameters',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunction', $responses[0]->action);
		$this->assertEquals('returnInputParameters', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));
	}

	public function testRequestDataAndReturnTransformation() {
		
		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunction',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertTrue(isset($responses[0]->result->data), 'Expected that $this->request->data is not null.');
		$this->assertTrue(is_array($responses[0]->result->data), 'Expected the data-result to be an array, instead got '.print_r($responses,true));
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
	 */
	public function testRequestDataAndReturnTransformation_WithRequestHandler() {
		// keep original values
		$http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : false;
		$content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : false;

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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertTrue(isset($responses[0]->result->data), 'Expected that $this->request->data is not null.');
		$this->assertFalse(is_string($responses[0]->result->data), 'Expected the data-result to be an array, instead got a string '.var_export($responses[0]->result->data,true));
		$this->assertTrue(is_array($responses[0]->result->data), 'Expected the data-result to be an array, instead got '.print_r($responses,true));
		$this->assertEquals('my param1', $responses[0]->result->data[0]);
		$this->assertEquals('my param2', $responses[0]->result->data[1]);
		
		// general response checks (check dispatcher, collections and transformers)
		$this->assertEquals('RemotableFunctionWithRequestHandler', $responses[0]->action);
		$this->assertEquals('returnInputRequestData', $responses[0]->method);
		$this->assertEquals('rpc', $responses[0]->type);
		$this->assertEquals(1, $responses[0]->tid);
		$this->assertEquals(1, count($responses));

		// tear down
		$_SERVER['HTTP_ACCEPT'] = $http_accept;
		$_SERVER['CONTENT_TYPE'] = $content_type;
	}

	/**
	 * Returns an AuthComponent to log in and out.
	 * @return AuthComponent
	 */
	private function getAuthComponent() {
        // setup the component collection
        $Collection = new ComponentCollection();

        // setup the auth component
		$auth = new AuthComponent($Collection, array());

		return $auth;
	}

	/**
	 * The redirect method does not make sense when using Bancha. Therefore
	 * if a redirect happens we want to throw an exception.
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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is '.print_r($responses,true));
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
	 */
	public function testAuthComponent_NotLoggedIn() {
		// make sure we are logged out
		$this->getAuthComponent()->logout();

		// Build a request like it looks in Ext JS.
		$rawPostData = json_encode(array(array(
			'action'		=> 'RemotableFunctionWithAuthComponent',
			'method'		=> 'returnInputRequestData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array('my param1', 'my param2'),
		)));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is '.print_r($responses,true));
		$this->assertEquals('BanchaAuthLoginException', $responses[0]->exceptionType);
	}

	/**
	 * Without any adoption the AuthComponent would trigger a redirect if the user
	 * is not authorized to use this method.
	 *
	 * Since it makes no sense to test this in a unit test, this provides an 
	 * integration test where we go though the Dispatcher to check if everything
	 * works as expected. 
	 */
	public function testAuthComponent_NotAuthorized() {
		// log in
		$this->getAuthComponent()->login(array('name'=>'LoggedIn'));

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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertTrue(isset($responses[0]->result->data), 'Expected that $this->request->data is not null.');
		$this->assertFalse(is_string($responses[0]->result->data), 'Expected the data-result to be an array, instead got a string '.var_export($responses[0]->result->data,true));
		$this->assertTrue(is_array($responses[0]->result->data), 'Expected the data-result to be an array, instead got '.print_r($responses,true));
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
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$this->assertEquals('exception', $responses[0]->type, 'Expected an exception, instead $responses is '.print_r($responses,true));
		$this->assertEquals('BanchaAuthAccessRightsException', $responses[0]->exceptionType);

		// tear down
		$this->getAuthComponent()->logout();
	}
}
