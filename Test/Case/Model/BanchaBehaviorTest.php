<?php
/**
 * BanchaBehaviorTest file
 *
 * PHP 5
 *
 *
 * @copyright     ???
 * @link          ???
 * @package       plugin.Bancha.Test.Model.Behavior
 * @since         Banche v 0.1
 * @license       GPLv3
 */
if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('BanchaBehavior', 'Bancha');
//require_once(dirname(dirname(__FILE__)) . DS . 'models.php');


/**
 * BanchaBehaviorTest class
 *
 */
class BanchaBehaviorTest extends CakeTestCase {
/**
 * Sets the plugins folder for this test
 *
 * @return void
 */
	public function setUp() {
		App::build(array(
			'plugins' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS)
		), true);
		App::objects('plugins', null, false);
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
		ClassRegistry::flush();
	}

/**
 * Tests one
 *
 * @return void
 */
	public function testMetaData() {
		
		#load fixtures
		$this->loadFixtures('TranslateTable', 'Tag', 'TranslatedItem', 'Translate', 'User', 'TranslatedArticle', 'TranslateArticle');
		
		#create Model
		$TestModel = new Model();
		
		#set Model Properties
		$TestModel->translateTable = 'another_i18n';
		
		#set Behavior
		$TestModel->Behaviors->attach('BanchaBehavior', array('MModel'));
		
		#execute function
		$translateModel = $TestModel->Behaviors->Bancha->translateModel($TestModel);
		
		#do the assertions
		$this->assertEqual($translateModel->name, 'I18nModel');
		$this->assertEqual($translateModel->useTable, 'another_i18n');
	}


/**
 * Tests that BanchaBehavior::extractMetaData() throws an exception on wrong somethinng
 *
 * @return void
 * @expectedException SomeException
 */
	public function extractBanchaMetaData(&$model) {
		CakePlugin::path('TestPlugin');
	}

}
