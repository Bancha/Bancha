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
//App::uses('plugin', 'Bancha');
//App::uses('Bancha', 'Behavior');
require_once(dirname(dirname(__FILE__)) . DS . 'Model' . DS . 'testmodels.php');  //here we get the testModel


/**
 * BanchaBehaviorTest class
 *
 */
class BanchaBehaviorTest extends CakeTestCase {
	/**
	 * fixtures
	 * @var unknown_type
	 */
	
	//var $fixtures = array( 'UserTest' ); 
/**
 * Sets the plugins folder for this test
 *
 * @return void
 */
	public function setUp() {
		//App::build(array('plugins' => array( 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS ), true));
		//App::objects('plugins', null, false);
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
 *  provider for testing the order attribute
 */

    public static function providerOrder()
    {
        return array(
          array(
          	array('Model.name' => 'DESC'), 
          	array(
          		array( 'property' => 'name', 'direction' => 'DESC' )
          	)
          ),
       // TODO write into TechDocu that only arrays are allowed for order behavior
    /*       array(
          	"field",
          	array(array( 'property' => 'name', 'direction' => 'DESC' ))),
          array(
          	"Model.field",
          	array(array( 'property' => 'filed', 'direction' => '' ))),
          array(
          	"Model.field asc",
          	array(array( 'property' => 'filed', 'direction' => '' ))),
         array(
          	"Model.field ASC",
          	array(array( 'property' => 'filed', 'direction' => '' ))),
          array(
          	"Model.field DESC",
          	array(array( 'property' => 'filed', 'direction' => '' ))), */
          array(
          	array("Model.field" => "asc", "Model.field2" => "DESC"),
          	array(
          		array( 'property' => 'field', 'direction' => 'asc' ),
          		array( 'property' => 'field2', 'direction' => 'DESC')
          		)
          	)
          	
        );
    }

/**
 * Tests order
 * @dataProvider providerOrder
 * @return void
 */
	public function testMetaDataOrder3($in=array(),$out=array()) {
		$TestModel = new TestUserOrder();
		$TestModel->order = $in;
		$TestModel->Behaviors->load('Bancha',array('Model'));
		
		$ExtJSdata = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
		$this->assertEqual($ExtJSdata['sorters'], $out);
	}
		
/**
 * provider for relationships
 */	
	public static function providerRelationships() {
		return array(
			//array('hasOne', 'table', array(array('hasOne' => 'table'))),
			array('in', 'out', 'test')
		);
	}
	
	/**
	 * Tests relationships with provider
	 * @dataProvider providerRelationships
	 */
	
	public function testMetaDataRealtionships2($type, $table, $out) {
		$TestModel = new TestUserRelationships();
		$TestModel->{$type} = $table;
		$TestModel->Behaviors->load('Bancha',array('Model'));
		$ExtJSdata = $TestModel->Behaviors->Bancha->extractBanchaMetaData();
		$this->assertEqual($ExtJSdata['associations'],array( array( 'hasMany' => 'Article')));
		
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
		
		//debug("This debug() output shows the structure of the returned array");
		//debug($ExtJSdata,true);
		#do the assertions
		$this->assertEquals(json_encode($ExtJSdata), '{"fields":[{"name":"id","type":"integer"},{"name":"name","type":"string"},{"name":"login","type":"string"},{"name":"created","type":"datetime"},{"name":"email","type":"string"},{"name":"avatar","type":"string"},{"name":"weight","type":"float"},{"name":"heigth","type":"float"}],"validations":[{"type":"length","name":"id","max":null},{"type":"length","name":"name","max":64},{"type":"length","name":"login","max":64},{"type":"length","name":"created","max":null},{"type":"length","name":"email","max":64},{"type":"length","name":"avatar","max":64},{"type":"length","name":"weight","max":null},{"type":"length","name":"heigth","max":null}],"associations":[{"hasMany":"Article"}],"sorters":[{"property":"order","direction":"ASC"}]}' );
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

