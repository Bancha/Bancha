<?php
/**
 * BanchaControllerTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */


/**
 * BanchaControllerTest
 * @package       Bancha
 * @category      tests
 */
class BanchaControllerTest extends ControllerTestCase {

	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	/**
	 * Keeps a reference to the default paths, since
	 * we need to change them in some tests here
	 * @var Array
	 */
	private $_defaultPaths = null;

	public function setUp() {
		// make sure there's no old cache
		Cache::clear(false,'_bancha_api_');

		// we will reset the default paths for controllers and model
		// the defaults get applied again in the tearDown
		$this->_defaultPaths = App::paths();

		// build up the test paths
		App::build(array(
			'Controller' => APP . DS . 'Controller' . DS,
			'Model' => APP . DS . 'Model' . DS
		), App::RESET);
	}

	public function tearDown() {
		App::build($this->_defaultPaths, App::RESET);

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

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that correct metadata is send
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

	public function testBanchaApiWithAllMetadata() {
		$response = $this->testAction('/bancha-api/models/all.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that correct metadata is send
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data
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

    