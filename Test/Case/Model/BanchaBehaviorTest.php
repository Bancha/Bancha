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
App::uses('Bancha', 'plugins');
//App::uses('Bancha', 'Behavior');
require_once(dirname(dirname(__FILE__)) . DS . 'Model' . DS . 'models.php');


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
/*		App::build(array(
			'plugins' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS)
		), true);
*/
		App::build(array(
			'plugins' => array(  'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS ), true));
		App::objects('plugins', null, false);
		App::build(array('Model/Behavior' => array(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' .DS)), App::RESET);
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
	//	$this->loadFixtures( 'Tag', 'TranslatedItem', 'Translate', 'User', 'TranslatedArticle', 'TranslateArticle');
		//$this->loadFixtures( 'User' );
		#create Model
		$TestModel = new TestModel();
		
		#set Model Properties
	//	$TestModel->translateTable = 'another_i18n';
		
		#set Behavior
		//$TestModel->Behaviors->load('BanchaBehavior', array('Model'));
		$TestModel->Behaviors->load('Bancha',array('Model'));
		
		#execute function
		$translateModel = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
		
		#do the assertions
		//$this->assertEqual($translateModel->name, 'I18nModel');
		//$this->assertEqual($translateModel->useTable, 'another_i18n');
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

/**
 * Short description for file.
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       cake.tests.fixtures
 * @since         CakePHP(tm) v 1.2.0.4667
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Short description for class.
 *
 * @package       cake.tests.fixtures
 */
class UserFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'User'
 * @access public
 */
	public $name = 'User';

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'user' => array('type' => 'string', 'null' => false),
		'password' => array('type' => 'string', 'null' => false),
		'created' => 'datetime',
		'updated' => 'datetime'
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'),
		array('user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'),
		array('user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'),
		array('user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'),
	);
}

