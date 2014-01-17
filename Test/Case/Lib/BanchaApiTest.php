<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Lib
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaApi', 'Bancha.Bancha');

/**
 * BanchaApiTest
 *
 * @package       Bancha.Test.Case.Lib
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class BanchaApiTest extends CakeTestCase {

	public $fixtures = array(
		'plugin.bancha.article',
		'plugin.bancha.articles_tag',
		'plugin.bancha.user',
		'plugin.bancha.tag'
	);

/**
 * Keeps a reference to the default paths, since
 * we need to change them in the setUp method.
 * 
 * @var array
 */
	protected $_originalPaths = null;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->_originalPaths = App::paths();

		// build up the test paths
		App::build(array(
			'Controller' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Controller' . DS,
			'Model' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Model' . DS,
			'Plugin' => App::pluginPath('Bancha') . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS
		), App::RESET);

		// load plugin from test_app
		CakePlugin::load('TestPlugin');

		// force the cache to renew
		App::objects('plugin', null, false);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		// tear down
		CakePlugin::unload('TestPlugin');

		// reset the paths
		App::build($this->_originalPaths, App::RESET);

		// force the cache to renew
		App::objects('plugin', null, false);
	}

/**
 * Test retrieving the remotable models
 *
 * @return void
 */
	public function testGetRemotableModels() {
		// prepare
		$api = new BanchaApi();
		$remotableModels = $api->getRemotableModels();

		// test app models for set
		$this->assertContains('Article', $remotableModels);
		$this->assertContains('User', $remotableModels);
		$this->assertContains('Tag', $remotableModels);
		$this->assertContains('ArticlesTag', $remotableModels);

		// test plugin models are set
		$this->assertContains('TestPlugin.Comment', $remotableModels);
	}

/**
 * Test filtering the remotable models
 *
 * @return void
 */
	public function testFilterRemotableModels() {
		// prepare
		$api = new BanchaApi();
		$remotableModels = array('Article', 'User', 'Tag', 'ArticlesTag', 'TestPlugin.Comment');

		// expose all remotable models
		$filteredModels = $api->filterRemotableModels($remotableModels, 'all');
		$this->assertCount(5, $filteredModels);
		$this->assertContains('Article', $filteredModels);
		$this->assertContains('ArticlesTag', $filteredModels);
		$this->assertContains('Tag', $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('TestPlugin.Comment', $filteredModels);

		// expose one model
		$filteredModels = $api->filterRemotableModels($remotableModels, '[User]');
		$this->assertCount(1, $filteredModels);
		$this->assertContains('User', $filteredModels);

		// expose one model (alternative syntax)
		$filteredModels = $api->filterRemotableModels($remotableModels, 'User');
		$this->assertCount(1, $filteredModels);
		$this->assertContains('User', $filteredModels);

		// expose two models
		$filteredModels = $api->filterRemotableModels($remotableModels, '[User,Article]');
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Article', $filteredModels);

		$filteredModels = $api->filterRemotableModels($remotableModels, '[ User, Article]');
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Article', $filteredModels);

		// expose two models (alternative syntax)
		$filteredModels = $api->filterRemotableModels($remotableModels, 'User,Article');
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Article', $filteredModels);

		// expose two models (alternative usage)
		$filteredModels = $api->filterRemotableModels($remotableModels, array('User', 'Article'));
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Article', $filteredModels);

		// expose no models
		$filteredModels = $api->filterRemotableModels($remotableModels, '');
		$this->assertCount(0, $filteredModels);

		// expose two models, one from plugin
		$filteredModels = $api->filterRemotableModels($remotableModels, '[User,TestPlugin.Comment]');
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('TestPlugin.Comment', $filteredModels);
	}

/**
 * filterRemotableModels() should throw a MissingModelException when a model is provided in $filter which is not
 * remotable model.
 *
 * @return void
 * @expectedException MissingModelException
 */
	public function testFilterRemotableModelsMissingModel() {
		$api = new BanchaApi();
		$api->filterRemotableModels(array(), '[InvalidModel]');
	}

/**
 * Tests if getMetadata returns meta data for all given models.
 *
 * @return void
 */
	public function testGetMetadata() {
		$api = new BanchaApi();
		$metadata = $api->getMetadata(array('User', 'Article', 'TestPlugin.Comment'));
		$this->assertCount(5, $metadata); // 3 models + 2 metadata properties
		$this->assertArrayHasKey('User', $metadata);
		$this->assertArrayHasKey('Article', $metadata);
		$this->assertArrayHasKey('TestPlugin.Comment', $metadata);
		$this->assertArrayHasKey('_UID', $metadata);
		$this->assertArrayHasKey('_ServerDebugLevel', $metadata);
		$this->assertTrue(is_array($metadata['User']));
		$this->assertTrue(is_array($metadata['Article']));
		$this->assertTrue(is_array($metadata['TestPlugin.Comment']));
		$this->assertTrue(strlen($metadata['_UID']) > 0);
	}

/**
 * Test getControllerClassByModelClass
 *
 * @return void
 */
	public function testGetControllerClassByModelClass() {
		$api = new BanchaApi();
		$this->assertEquals('UsersController', $api->getControllerClassByModelClass('User'));
		$this->assertEquals('TestPlugin.CommentsController', $api->getControllerClassByModelClass('TestPlugin.Comment'));
	}

/**
 * Test getCrudActionsOfController
 *
 * @return void
 */
	public function testGetCrudActionsOfController() {
		$api = new BanchaApi();

		// test app controller with full CRUD
		$crudActions = $api->getCrudActionsOfController('UsersController');
		$this->assertCount(6, $crudActions);
		$this->assertEquals('getAll', $crudActions[0]['name']);
		$this->assertEquals(0, $crudActions[0]['len']);
		$this->assertEquals('read', $crudActions[1]['name']);
		$this->assertEquals(1, $crudActions[1]['len']);
		$this->assertEquals('submit', $crudActions[5]['name']);
		$this->assertEquals(1, $crudActions[5]['len']);
		$this->assertEquals(true, $crudActions[5]['formHandler']);

		// test plugin controller with create, read, update
		$crudActions = $api->getCrudActionsOfController('TestPlugin.CommentsController');
		$this->assertCount(5, $crudActions);
		$this->assertEquals('getAll', $crudActions[0]['name']);
		$this->assertEquals(0, $crudActions[0]['len']);
		$this->assertEquals('read', $crudActions[1]['name']);
		$this->assertEquals(1, $crudActions[1]['len']);
		$this->assertEquals('submit', $crudActions[4]['name']);
		$this->assertEquals(1, $crudActions[4]['len']);
		$this->assertEquals(true, $crudActions[4]['formHandler']);

		// test plugin controller with no CRUD method
		App::uses('PluginTestsController', 'TestPlugin.Controller');
		$crudActions = $api->getCrudActionsOfController('TestPlugin.PluginTestsController');
		$this->assertCount(0, $crudActions);
	}

/**
 * Test getRemotableMethods
 *
 * @return void
 */
	public function testGetRemotableMethods() {
		// prepare
		$api = new BanchaApi();
		$remotableMethods = $api->getRemotableMethods();
		$this->assertCount(2, $remotableMethods);

		// test app controller
		$this->assertContains('HelloWorld', array_keys($remotableMethods));
		$this->assertCount(2, $remotableMethods['HelloWorld']);
		$this->assertEquals('hello', $remotableMethods['HelloWorld'][0]['name']);
		$this->assertEquals(0, $remotableMethods['HelloWorld'][0]['len']);
		$this->assertEquals('helloyou', $remotableMethods['HelloWorld'][1]['name']);
		$this->assertEquals(2, $remotableMethods['HelloWorld'][1]['len']);

		// test plugin controller
		$this->assertContains('TestPlugin.PluginTest', array_keys($remotableMethods));
		$this->assertCount(1, $remotableMethods['TestPlugin.PluginTest']);
		$this->assertEquals('exposedTestMethod', $remotableMethods['TestPlugin.PluginTest'][0]['name']);
		$this->assertEquals(0, $remotableMethods['HelloWorld'][0]['len']);
	}

/**
 * Test getRemotableModelActions
 *
 * @return void
 */
	public function testGetRemotableModelActions() {
		$api = new BanchaApi();

		// this is simply a wrapper function, se very simple testing is sufficient
		$remotableActions = $api->getRemotableModelActions(array('Article', 'TestPlugin.Comment'));
		$this->assertCount(2, $remotableActions);

		// test app model
		$this->assertCount(6, $remotableActions['Article']);
		$this->assertEquals('getAll', $remotableActions['Article'][0]['name']);

		// test plugin model
		$this->assertCount(5, $remotableActions['TestPlugin.Comment']); // (comments does not support delete)
		$this->assertEquals('getAll', $remotableActions['TestPlugin.Comment'][0]['name']);
	}
}
