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
    public $fixtures = array('plugin.bancha.article','plugin.bancha.articles_tag','plugin.bancha.user','plugin.bancha.tag');

	public function testGetRemotableModels() {
		$api = new BanchaApi();
		$remotableModels = $api->getRemotableModels();
		$this->assertContains('Article', $remotableModels);
		$this->assertContains('User', $remotableModels);
		$this->assertContains('Tag', $remotableModels);
		$this->assertContains('ArticlesTag', $remotableModels);
	}

	public function testFilterRemotableModels()
	{
		$api = new BanchaApi();
		$remotableModels = array('Article', 'User', 'Tag', 'ArticlesTag');
		// expose all remotable models
		$filteredModels = $api->filterRemotableModels($remotableModels, 'all');
		$this->assertCount(4, $filteredModels);
		$this->assertContains('Article', $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Tag', $filteredModels);
		$this->assertContains('ArticlesTag', $filteredModels);

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
		$filteredModels = $api->filterRemotableModels($remotableModels, array('User','Article'));
		$this->assertCount(2, $filteredModels);
		$this->assertContains('User', $filteredModels);
		$this->assertContains('Article', $filteredModels);

		// expose no models
		$filteredModels = $api->filterRemotableModels($remotableModels, '');
		$this->assertCount(0, $filteredModels);
	}

	/**
	 * filterRemotableModels() should throw a MissingModelException when a model is provided in $filter which is not
	 * remotable model.
	 * @expectedException MissingModelException
	 */
	public function testFilterRemotableModels_MissingModel()
	{
		$api = new BanchaApi();
		$api->filterRemotableModels(array(), '[InvalidModel]');
	}

	/**
	 * Tests if returns meta data returns meta data for all given models.
	 */
	public function testGetMetadata()
	{
		$api = new BanchaApi();
		$metadata = $api->getMetadata(array('User', 'Article'));
		$this->assertCount(4, $metadata);
		$this->assertArrayHasKey('User', $metadata);
		$this->assertArrayHasKey('Article', $metadata);
		$this->assertArrayHasKey('_UID', $metadata);
		$this->assertArrayHasKey('_ServerDebugLevel', $metadata);
		$this->assertTrue(is_array($metadata['User']));
		$this->assertTrue(is_array($metadata['Article']));
		$this->assertTrue(strlen($metadata['_UID']) > 0);
	}

	public function testGetControllerClassByModelClass()
	{
		$api = new BanchaApi();
		$this->assertEquals('UsersController', $api->getControllerClassByModelClass('User'));
	}

	public function testGetCrudActionsOfController()
	{
		$api = new BanchaApi();
		$crudActions = $api->getCrudActionsOfController('UsersController');
		$this->assertCount(6, $crudActions);
		$this->assertEquals('getAll', $crudActions[0]['name']);
		$this->assertEquals(0, $crudActions[0]['len']);
		$this->assertEquals('read', $crudActions[1]['name']);
		$this->assertEquals(1, $crudActions[1]['len']);
		$this->assertEquals('submit', $crudActions[5]['name']);
		$this->assertEquals(1, $crudActions[5]['len']);
		$this->assertEquals(true, $crudActions[5]['formHandler']);
	}

	public function testGetRemotableMethods()
	{
		$api = new BanchaApi();
		$remotableMethods = $api->getRemotableMethods();
		$this->assertCount(2, $remotableMethods['HelloWorld']);
		$this->assertEquals('hello', $remotableMethods['HelloWorld'][0]['name']);
		$this->assertEquals(0, $remotableMethods['HelloWorld'][0]['len']);
		$this->assertEquals('helloyou', $remotableMethods['HelloWorld'][1]['name']);
		$this->assertEquals(2, $remotableMethods['HelloWorld'][1]['len']);
	}

	/**
	 * description
	 */
	public function testGetRemotableModelActions()
	{
		$api = new BanchaApi();
		$remotableActions = $api->getRemotableModelActions($api->getRemotableModels());
		$this->assertCount(4, $remotableActions);
		$this->assertCount(6, $remotableActions['Article']);
		$this->assertEquals('getAll', $remotableActions['Article'][0]['name']);
	}
}
