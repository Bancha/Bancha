<?php
/**
 * BanchaControllerTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Controller
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaControllerTest
 *
 * @package       Bancha.Test.Case.Controller
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class BanchaControllerTest extends ControllerTestCase {

	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	/**
	 * Keeps a reference to the default paths, since
	 * we need to change them in teh setUp method
	 * @var Array
	 */
	private $originalPaths = null;
	private $originalOrigin;
	private $originalDebugLevel;

	public function setUp() {
		parent::setUp();

		$this->originalDebugLevel = Configure::read('debug');
		$this->originalPaths = App::paths();

		// make sure there's no old cache
		Cache::clear(false,'_bancha_api_');

		// build up the test paths
		App::build(array(
			'Controller' => APP . DS . 'Controller' . DS,
			'Model' => APP . DS . 'Model' . DS
		), App::RESET);
	}

	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->originalDebugLevel);

		// reset the paths
		App::build($this->originalPaths, App::RESET);

		// make sure to flush after tests, so that real app is not influenced
		Cache::clear(false,'_bancha_api_');
	}

	public function testBanchaApiConfiguration() {
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check primary Bancha configurations
		$this->assertTrue(isset($api->metadata->_UID));
		$this->assertEquals(Configure::read('debug'), $api->metadata->_ServerDebugLevel);

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));
	}

	public function testBanchaApiWithOneModelMetadata() {
		$response = $this->testAction('/bancha-api/models/User.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check that all direct methods are exposed
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that only requested metadata is send
		$this->assertFalse(isset($api->metadata->Article));
		$this->assertFalse(isset($api->metadata->ArticlesTag));
		$this->assertFalse(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User)); // <-- this should be available
		$this->assertFalse(isset($api->metadata->HelloWorld));
		$this->assertFalse(isset($api->metadata->Bancha));

		// test meta data structure
		$this->assertEquals('id', $api->metadata->User->idProperty);
		$this->assertTrue(is_array($api->metadata->User->fields));
		$this->assertTrue(is_array($api->metadata->User->validations));
		$this->assertTrue(is_array($api->metadata->User->associations));
		$this->assertTrue(is_array($api->metadata->User->sorters));

	}

	public function testBanchaApiWithMultipleMetadata() {
		$response = $this->testAction('/bancha-api/models/[Article,User].js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check that all direct methods are exposed
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that only requested metadata is send
		$this->assertTrue(isset($api->metadata->Article)); // <-- this should be available
		$this->assertFalse(isset($api->metadata->ArticlesTag));
		$this->assertFalse(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User)); // <-- this should be available
		$this->assertFalse(isset($api->metadata->HelloWorld));
		$this->assertFalse(isset($api->metadata->Bancha));
	}

	public function testBanchaApiWithAllMetadata() {
		$response = $this->testAction('/bancha-api/models/all.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check that all direct methods are exposed
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that only requested metadata is send
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data
	}

	public function testBanchaApiClassWithNoMetaData() {
		// get response without models
		$response = $this->testAction('/bancha-api-class.js');

		// the api starts with Ext.define('Bancha.REMOTE_API',
		$this->assertEquals('Ext.define(\'Bancha.REMOTE_API\',', substr($response, 0, 31));
		// get the api data
		$api = substr($response, 31); // remove the define in the beginning
		$api = substr($api, 0, strpos($api, ');'));
		$api = json_decode($api);

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);
		$this->assertEquals(true, $api->singleton);

		// check that no metadata is send
		$this->assertFalse(isset($api->metadata->Article));
		$this->assertFalse(isset($api->metadata->ArticlesTag));
		$this->assertFalse(isset($api->metadata->Tag));
		$this->assertFalse(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data
	}

	public function testBanchaApiClassWithAllMetaData() {
		// get response with models
		$response = $this->testAction('/bancha-api-class/models/all.js');

		// the api starts with Ext.define('Bancha.REMOTE_API',
		$this->assertEquals('Ext.define(\'Bancha.REMOTE_API\',', substr($response, 0, 31));
		// get the api data
		$api = substr($response, 31); // remove the define in the beginning
		$api = substr($api, 0, strpos($api, ');'));
		$api = json_decode($api);

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);
		$this->assertEquals(true, $api->singleton);

		// check that only requested metadata is send
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data
	}

	public function testBanchaApiClassBeautifiedCode() {

		// in debug mode expect the output to be indented
		Configure::write('debug', 2);
		$response = $this->testAction('/bancha-api-class.js');
		// there should be some indent code
		$this->assertContains('   ', $response, 'Remote API output should be readable code, instead find minified code.');

		// in production mode expect the output to be minified
		Configure::write('debug', 0);
		$response = $this->testAction('/bancha-api-class.js');
		// there should be no indent in code
		$this->assertEquals(0, preg_match('/  /', $response), 'Remote API output should be minified code, instead find beautified code.');
	}

	public function testBanchaApiPackaged() {
		// get response with models, packaged
		$response = $this->testAction('/bancha-api-packaged/models/all.js');

		// the api starts with Ext.define('Bancha.REMOTE_API',
		$this->assertEquals('Ext.define(\'Bancha.REMOTE_API\',', substr($response, 0, 31));
		// get the api data
		$api = substr($response, 31); // remove the define in the beginning
		$api = substr($api, 0, strpos($api, ');'));
		$api = json_decode($api);

		// check basic configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);
		$this->assertEquals(true, $api->singleton);

		// check that all direct methods are exposed
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that all metadata is exposed
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data

		// ok, now to the interesting part
		// find all defines
		$classes = new stdClass();
		$defines = explode('Ext.define(', $response);
		array_shift($defines); // first is empty, since it starts with Ext.define
		array_shift($defines); // second, the remote api
		foreach ($defines as $define) {
			$class = substr($define, 14); // remove: 'Bancha.model.
			$class = substr($class, 0, strpos($class, "'")); // only get the name
			$classes->{$class} = true;
		}

		// check that all classes are defined
		$this->assertTrue(isset($classes->Article));
		$this->assertTrue(isset($classes->ArticlesTag));
		$this->assertTrue(isset($classes->Tag));
		$this->assertTrue(isset($classes->User));
		$this->assertFalse(isset($classes->HelloWorld)); // there is no exposed model
		$this->assertFalse(isset($classes->Bancha)); // there is no exposed model
	}

	public function testLoadModelMetaData_Ajax_One() {
		$response = $this->testAction('/bancha-load-metadata/User.js');
		$data = json_decode($response);

		// check that only requested metadata is send
		$this->assertFalse(isset($data->Article));
		$this->assertFalse(isset($data->ArticlesTag));
		$this->assertFalse(isset($data->Tag));
		$this->assertTrue(isset($data->User)); // <-- this should be available
	}

	public function testLoadModelMetaData_Ajax_Multiple() {
		$response = $this->testAction('/bancha-load-metadata/[User,Article].js');
		$data = json_decode($response);

		// check that only requested metadata is send
		$this->assertTrue(isset($data->Article)); // <-- this should be available
		$this->assertFalse(isset($data->ArticlesTag));
		$this->assertFalse(isset($data->Tag));
		$this->assertTrue(isset($data->User)); // <-- this should be available
	}

	public function testLoadModelMetaData_Ajax_All() {
		$response = $this->testAction('/bancha-load-metadata/all.js');
		$data = json_decode($response);

		// check that only requested metadata is send
		$this->assertTrue(isset($data->Article)); // <-- this should be available
		$this->assertTrue(isset($data->ArticlesTag));
		$this->assertTrue(isset($data->Tag));
		$this->assertTrue(isset($data->User)); // <-- this should be available
	}

	/**
	 * AJAX requests to invalid models should throw an exception,
	 * so that Ext.Ajax triggers the failure routine.
	 *
	 * @expectedException MissingModelException
	 */
	public function testLoadModelMetaData_Ajax_Error() {
		$this->testAction('/bancha-load-metadata/[Imaginary].js');
	}

	/**
	 * Bancha via Ext.Direct requests send the data in a different peroperty
	 * and also expects the result in a different format, check this.
	 */
	public function testLoadModelMetaData_ExtDirect_Multiple() {
		// we can't fake an Bancha request that easily,
		// (We would need to set $this->params['isBancha'])
		// therefore we make a system test here
		$rawPostData = json_encode(array(array(
			'action'		=> 'Bancha',
			'method'		=> 'loadMetaData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array("[Article,User]"),
		)));

		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check the basic response and the result property
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertEquals(true, $responses[0]->result->success);

		// check that only requested metadata is send
		$data = $responses[0]->result->data;
		$this->assertTrue(isset($data->Article)); // <-- this should be available
		$this->assertFalse(isset($data->ArticlesTag));
		$this->assertFalse(isset($data->Tag));
		$this->assertTrue(isset($data->User)); // <-- this should be available
	}

	/**
	 * Bancha via Ext.Direct requests to invalid models should return
	 * a result with success false, but should not throw a client-side
	 * exception, because Ext.loader.Models should be able to handle
	 * the error.
	 */
	public function testLoadModelMetaData_ExtDirect_Error() {
		// we can't fake an Bancha request that easily,
		// (We would need to set $this->params['isBancha'])
		// therefore we make a system test here
		$rawPostData = json_encode(array(array(
			'action'		=> 'Bancha',
			'method'		=> 'loadMetaData',
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> array("[Imaginary]"),
		)));

		$dispatcher = new BanchaDispatcher();
		$collection = new BanchaRequestCollection($rawPostData);
		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// test
		$responses = json_decode($dispatcher->dispatch($collection, $response, array('return' => true)));

		// check the basic response, the result property and message
		$this->assertTrue(isset($responses[0]->result), 'Expected an result for first request, instead $responses is '.print_r($responses,true));
		$this->assertEquals(false, $responses[0]->result->success);
		$this->assertEquals('Model Imaginary could not be found.', $responses[0]->result->message);
	}

	public function testBanchaApiServerErrorProperty_NoError() {
		$debugLevel = Configure::read('debug');

		// build up the app folders
		App::build(array(
			'Controller' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Controller' . DS,
			'Model' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Model' . DS,
		), true);

		// in production mode only expect a flag
		Configure::write('debug', 0);
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		// test data
		$this->assertEquals(0, $api->metadata->_ServerDebugLevel);
		$this->assertFalse($api->metadata->_ServerError);

		// in debug mode expect a error message
		Configure::write('debug', 2);
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		// test data
		$this->assertEquals(2, $api->metadata->_ServerDebugLevel);
		$this->assertFalse($api->metadata->_ServerError);

		// reset level to normal
		Configure::write('debug', $debugLevel);
	}

	/**
	 * In this test there are models, but the controllers are missing.
	 * This should result into an error flag in the Bancha-Api.
	 *
	 * We will not need a separate test_app model folder here anymore
	 * after we have refactored the static App::objects out of the
	 * BanchaApi library class.
	 */
	public function testBanchaApiServerErrorProperty_MissingControllerError() {
		$debugLevel = Configure::read('debug');

		// build up the app folders to provoke an error
		App::build(array(
			'Model' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Model_BanchaApi_MissingController' . DS,
		), App::RESET);

		// in production mode only expect a flag
		Configure::write('debug', 0);
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		// test data
		$this->assertEquals(0, $api->metadata->_ServerDebugLevel);
		$this->assertTrue($api->metadata->_ServerError);

		// in debug mode expect a error message
		Configure::write('debug', 2);
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		// test data
		$this->assertEquals(2, $api->metadata->_ServerDebugLevel);
		$this->assertTrue(is_string($api->metadata->_ServerError));
		$this->assertTrue(strlen($api->metadata->_ServerError) > 1);

		// reset level to normal
		Configure::write('debug', $debugLevel);
	}
}


