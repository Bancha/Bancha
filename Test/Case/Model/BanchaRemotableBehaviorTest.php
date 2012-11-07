<?php
/**
 * BanchaRemotableBehaviorTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::uses('AppModel', 'Model');
require_once(dirname(__FILE__) . DS . 'testmodels.php');  //here we get the testModel


/**
 * BanchaRemotableBehaviorTest class
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 *
 */
class BanchaRemotableBehaviorTest extends CakeTestCase {
	public $fixtures = array('plugin.bancha.article_for_testing_save_behavior','plugin.bancha.user');
/**
 * Sets the plugins folder for this test
 *
 * @return void
 */
	public function setUp() {
		//App::build(array('plugins' => array( 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS ), true));
		//App::objects('plugins', null, false);
		App::build(array(
			'Model/Behavior' => array(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' .DS)),
			App::RESET
		);
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
		$TestModel->Behaviors->load('Bancha.BanchaRemotable',array('Model'));
		
		$ExtJSdata = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
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
		$TestModel->Behaviors->load('Bancha.BanchaRemotable',array('Model'));
		$ExtJSdata = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
		$this->assertEqual($ExtJSdata['associations'],array( array( 'type' => 'hasMany', 'model' => 'Bancha.model.Article', 'foreignKey' => 'user_id', 'name' => 'articles', 'getterName' => 'articles', 'setterName' => 'setArticles')));
				
	}

/**
 * Tests relationships
 *
 * @return void
 */
	public function testMetaDataRelationships() {
		$TestModel = new TestUserRelationships();		
		$TestModel->Behaviors->load('Bancha.BanchaRemotable',array('Model'));
		
		$ExtJSdata = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
				
		$this->assertEqual($ExtJSdata['associations'],array( array( 'type' => 'hasMany', 'model' => 'Bancha.model.Article', 'foreignKey' => 'user_id', 'name' => 'articles', 'getterName' => 'articles', 'setterName' => 'setArticles')));
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
		$TestModel->Behaviors->load('Bancha.BanchaRemotable',array('Model'));
		
		#execute function
		$ExtJSdata = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
		
		//debug("This debug() output shows the structure of the returned array");
		//debug($ExtJSdata,true);
		#do the assertions
		//$this->assertEquals(json_encode($ExtJSdata), '{"idProperty":"id","fields":[{"name":"id","type":"integer"},{"name":"name","type":"string"},{"name":"login","type":"string"},{"name":"created","type":"datetime"},{"name":"email","type":"string"},{"name":"avatar","type":"string"},{"name":"weight","type":"float"},{"name":"heigth","type":"float"}],"validations":[{"type":"length","name":"id","max":null},{"type":"length","name":"name","max":64},{"type":"length","name":"login","max":64},{"type":"length","name":"created","max":null},{"type":"length","name":"email","max":64},{"type":"length","name":"avatar","max":64},{"type":"length","name":"weight","max":null},{"type":"length","name":"heigth","max":null}],"associations":[{"hasMany":"Article"}],"sorters":[{"property":"order","direction":"ASC"}]}' );
		$this->assertEquals(true,  true);
	}


/**
 * unused
 *
 * Tests that BanchaRemotableBehavior::extractMetaData() throws an exception on wrong somethinng
 *
 * @return void
 * @expectedException SomeException
 */
	public function extractBanchaMetaData(&$model) {
		CakePlugin::path('TestPlugin');
	}


	/**
	 * Tests that custom validation rule swhich execute a find doesn't break bancha
	 * see issue: https://github.com/Bancha/Bancha/issues/22
	 * @return void
	 */
	public function testModelSave() {
		$article = ClassRegistry::init('ArticleForTestingSaveBehavior');
		
		// save article
		$article->create();
		$this->assertTrue(!!$article->saveFieldsAndReturn(array(
			'ArticleForTestingSaveBehavior' => array(
				'title' => 'testModelSave Entry',
				'body' => 'This is the body text.',
				'user_id' => 95
			)
		)));
		
		// second article with same title
		$article->create();
		$this->assertTrue(!!$article->saveFieldsAndReturn(array(
			'ArticleForTestingSaveBehavior' => array(
				'title' => 'testModelSave Entry',
				'body' => 'This is the body text.',
				'user_id' => 95
			)
		)));
		
		// validation should fail here
		// check that the validation rule is actually working
		$article->create();
		$article->set(array(
			'ArticleForTestingSaveBehavior' => array(
				'title' => 'testModelSave Entry',
				'body' => 'This is the body text.',
				'user_id' => 95
			)
		));
		$this->assertFalse($article->validates());
		
	}

}

