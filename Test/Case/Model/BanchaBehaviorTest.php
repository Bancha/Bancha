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

/***** ????? *****/
//App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('Bancha', 'plugins');
//App::uses('Bancha', 'Behavior');
require_once(dirname(dirname(__FILE__)) . DS . 'Model' . DS . 'testmodels.php');  //here we get the testModel


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
		App::build(array('plugins' => array( 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS ), true));
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
 * Tests order
 *
 * @return void
 */
	public function testMetaDataOrder() {
		$TestModel = new TestUserOrder();
		$TestModel->Behaviors->load('Bancha',array('Model'));
		
		$ExtJSdata = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
		
		$this->assertEqual($ExtJSdata['sorters'][0]['property'], 'name');
		$this->assertEqual($ExtJSdata['sorters'][0]['direction'], 'ASC');
		}
		
/**
 * Tests relationships
 *
 * @return void
 */
	public function testMetaDataRelationships() {
		$TestModel = new TestUserRelationships();		
		$TestModel->Behaviors->load('Bancha',array('Model'));
		
		$ExtJSdata = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
				
		$this->assertEqual($ExtJSdata['associations'],array( array( 'hasMany' => 'Article')));
		}
	
/**
 * general Test that prints out the array
 *
 * @return void
 */
	public function testMetaData() {
		
		#load fixtures
		//$this->loadFixtures( 'User' );
		
		#create Model
		$TestModel = new TestUser();
		
		#set Model Properties
		
		#set Behavior
		$TestModel->Behaviors->load('Bancha',array('Model'));
		
		#execute function
		$ExtJSdata = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
		
		debug("This debug() output shows the structure of the returned array");
		debug($ExtJSdata,true);
		#do the assertions
		$this->assertEquals(json_encode($ExtJSdata), '{"fields":[{"name":"id","type":"integer"},{"name":"name","type":"string"},{"name":"login","type":"string"},{"name":"created","type":"datetime"},{"name":"email","type":"string"},{"name":"avatar","type":"string"},{"name":"weight","type":"float"},{"name":"heigth","type":"float"}],"validations":[{"type":"length","name":"id","max":null},{"type":"length","name":"name","max":64},{"type":"length","name":"login","max":64},{"type":"length","name":"created","max":null},{"type":"length","name":"email","max":64},{"type":"length","name":"avatar","max":64},{"type":"length","name":"weight","max":null},{"type":"length","name":"heigth","max":null}],"associations":[{"hasMany":"Article"}],"sorters":[{"property":"name","direction":"ASC"}]}' );
	}


/**
 * unused

 * Tests that BanchaBehavior::extractMetaData() throws an exception on wrong somethinng
 *
 * @return void
 * @expectedException SomeException
 */
	public function extractBanchaMetaData(&$model) {
		CakePlugin::path('TestPlugin');
	}

}

