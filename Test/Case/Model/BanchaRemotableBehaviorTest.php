<?php
/**
 * BanchaRemotableBehaviorTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 * @copyright     Copyright 2011-2013 StudioQ OG
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
	public $fixtures = array('plugin.bancha.article','plugin.bancha.article_for_testing_save_behavior','plugin.bancha.user');
	private $standardDebugLevel;

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
	 * Test if the internally used _getExposedFields method always
	 * calculates the correct set of fields to expose.
	 * 
	 * @dataProvider testGetExposedFieldsDataProvider
	 */
	public function testGetExposedFields($behaviorConfig, $expecedResult) {
		$TestModel= ClassRegistry::init('Article');
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);
		
		$result = $TestModel->Behaviors->BanchaRemotable->_getExposedFields($TestModel);
		$this->assertEquals($result , $expecedResult);
	}
	/**
	 * Data Provider for testGetExposedFields
	 */
	public function testGetExposedFieldsDataProvider() {
		return array(
			array(
				array(), // default config
				array('id', 'title', 'date', 'body', 'published', 'user_id')
			),
			array(
				array('excludedFields' => null),
				array('id', 'title', 'date', 'body', 'published', 'user_id')
			),
			array(
				array('excludedFields' => array('body')),
				array('id', 'title', 'date', 'published', 'user_id')
			),
			array(
				array('exposedFields' => array('date', 'body')),
				array('date', 'body')
			),
			array(
				array('exposedFields' => array('date', 'body', 'published'), 'excludedFields' => array()),
				array('date', 'body', 'published')
			),
			array(
				array('exposedFields' => array('date', 'body', 'published'), 'excludedFields' => array('published')),
				array('date', 'body')
			)
		);
	}
	/**
	 * Test if the internally used _getExposedFields method 
	 * throws exceptions if misconfigured in debug mode
	 * 
	 * @dataProvider testGetExposedFieldsDataProvider_Exceptions
	 * @expectedException CakeException
	 */
	public function testGetExposedFields_Exceptions($behaviorConfig) {
		// turn debug mode on
		$this->standardDebugLevel = Configure::read('debug');
		Configure::write('debug', 2);

		// run test
		$TestModel= ClassRegistry::init('Article');
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		// trigger exception
		$result = $TestModel->Behaviors->BanchaRemotable->_getExposedFields($TestModel);

		// turn debug mode back to default
		Configure::write('debug', $this->standardDebugLevel);
	}
	/**
	 * Data Provider for testGetExposedFields_Exceptions
	 * The field names do not exist
	 */
	public function testGetExposedFieldsDataProvider_Exceptions() {
		return array(
			array(
				array('exposedFields' => array('imaginary')),
			),
			array(
				array('exposedFields' => array('date', 'imaginary', 'body')),
			),
			array(
				array('excludedFields' => array('imaginary')),
			),
			array(
				array('excludedFields' => array('date', 'imaginary', 'body')),
			),
			array(
				array(
					'exposedFields' => array('date', 'imaginary', 'body'),
					'excludedFields' => array('date', 'imaginary', 'body')),
			),
		);
	}

	/**
	 * Test if that isExposedField already returns the corret value 
	 * from given behvor config.
	 * 
	 * @dataProvider testIsExposedFieldDataProvider
	 */
	public function testIsExposedField($behaviorConfig, $fieldName, $expecedResult) {
		$TestModel= ClassRegistry::init('Article');
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);
		
		$result = $TestModel->Behaviors->BanchaRemotable->isExposedField($TestModel, $fieldName);
		$this->assertEquals($result , $expecedResult);
	}
	/**
	 * Data Provider for testIsExposedField
	 */
	public function testIsExposedFieldDataProvider() {
		return array(
			array(
				array(),
				'title',
				true
			),
			array(
				array('excludedFields' => array('title')),
				'title',
				false
			),
			array(
				array('exposedFields' => array('date', 'body')),
				'title',
				false
			)
		);
	}


	/**
	 * Get all associations in ExtJS/Sencha Touch format
	 */
	public function testGetAssociated() {

		// hasAndBelongsTo associations should be hidden
		$expecedResult = array(
			array(
				'type' => 'belongsTo',
				'model' => 'Bancha.model.User',
				'foreignKey' => 'user_id',
				'getterName' => 'getUser',
				'setterName' => 'setUser',
				'name' => 'user',
			),
			array(
				'type' => 'hasMany',
				'model' => 'Bancha.model.HasManyModel',
				'foreignKey' => 'article_id',
				'getterName' => 'hasManyModels',
				'setterName' => 'setHasManyModels',
				'name' => 'hasManyModels',
			),
			array(
				'type' => 'hasMany',
				'model' => 'Bancha.model.ArticleTag',
				'foreignKey' => 'article_id',
				'getterName' => 'articleTags',
				'setterName' => 'setArticleTags',
				'name' => 'articleTags',
			),
		);

		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());
		
		$result = $TestModel->Behaviors->BanchaRemotable->getAssociated($TestModel);
		$this->assertEqual(count($result), 3, "Expected three associations, instead got ".count($result));
		$this->assertEqual($result, $expecedResult);
	}


	/**
	 * Get all associations in ExtJS/Sencha Touch format.
	 * Filter out belongTo associations of non-exposed fields.
	 */
	public function testGetAssociated_Filtered() {

		// the belongsTo association should be hidden 
		// because the field user_id is hidden
		$expecedResult = array(
			array(
				'type' => 'hasMany',
				'model' => 'Bancha.model.HasManyModel',
				'foreignKey' => 'article_id',
				'getterName' => 'hasManyModels',
				'setterName' => 'setHasManyModels',
				'name' => 'hasManyModels',
			),
			array(
				'type' => 'hasMany',
				'model' => 'Bancha.model.ArticleTag',
				'foreignKey' => 'article_id',
				'getterName' => 'articleTags',
				'setterName' => 'setArticleTags',
				'name' => 'articleTags',
			),
		);

		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array('excludedFields'=>array('user_id')));
		
		$result = $TestModel->Behaviors->BanchaRemotable->getAssociated($TestModel);
		$this->assertEqual(count($result), 2, "Expected three associations, instead got ".count($result));
		$this->assertEqual($result, $expecedResult);
	}


	/**
	 * Get all sorters in ExtJS/Sencha Touch format.
	 * @dataProvider testGetSorterDataProvider
	 */
	public function testGetSorter($rules, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array('excludedFields'=>array('user_id')));

		// prepare sort rule
		$TestModel->order = $rules;
		
		$result = $TestModel->Behaviors->BanchaRemotable->getSorters($TestModel);
		$this->assertEqual($result, $expecedResult);
	}
	/**
	 * Data Provider for testGetSorter
	 */
	public function testGetSorterDataProvider() {
		return array(
			array(
			  	array('Model.name' => 'DESC'), 
			  	array(
			  		array('property' => 'name', 'direction' => 'DESC')
			  	)
			),
			array(
			  	array('Model.field' => 'ASC', 'Model.field2' => 'DESC'),
			  	array(
			  		array('property' => 'field', 'direction' => 'ASC'),
			  		array('property' => 'field2', 'direction' => 'DESC')
			  	)
			)
	   // TODO write into TechDocu that only arrays are allowed for order behavior
	/*	   array(
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
		  	
		);
	}


	/**
	 * extractBanchaMetaData basically wraps different functions without internal logic,
	 * so execute only a small integrational test.
	 */
	public function testExtractBanchaMetaData() {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$result = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);

		// check results
		$this->assertEqual($result['idProperty'], 'id');
		$this->assertEqual(count($result['fields']), 6);
		$this->assertEqual(count($result['validations']), 3);
		$this->assertEqual(count($result['associations']), 3);
		$this->assertEqual(count($result['sorters']), 1);
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


	public function providerExposeAndExclude() {
		return array(
			array(
				array(""),
				array(
					array(
						'name' => 'id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
					array(
						'name' => 'title',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'string',
					),
					array(
						'name' => 'date',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'date',
						'dateFormat' => 'Y-m-d H:i:s',
					),
					array(
						'name' => 'body',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'string',
					),
					array(
						'name' => 'published',
						'allowNull' => '1',
						'defaultValue' => '0',
						'type' => 'boolean',
					),
					array(
						'name' => 'user_id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					)
				)
			),
			array(
				array('excludedFields' => 'body'),
								array(
					array(
						'name' => 'id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
					array(
						'name' => 'title',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'string',
					),
					array(
						'name' => 'date',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'date',
						'dateFormat' => 'Y-m-d H:i:s',
					),
					array(
						'name' => 'published',
						'allowNull' => '1',
						'defaultValue' => '0',
						'type' => 'boolean',
					),
					array(
						'name' => 'user_id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					)
				)
			),
			array(
				array('exposedFields' => 'body'),
				array(
					array(
						'name' => 'body',
						'allowNull' => '1',
						'defaultValue' => '',
						'type' => 'string',
					)
				)
			)
		);
	}
/**
 * Test expose and exclude
 * This is component Test, more finegrained UnitTests exist too
 *
 * @dataProvider providerExposeAndExclude
 * @return void
 *
	public function testExposeAndExclude($behaviorConfig=array(),$result=array()) {
		$TestModel= ClassRegistry::init('Articles');
//		$TestModel = new TestUserRelationships();		
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable',$behaviorConfig);
		
		$ExtJSdata = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
		$this->assertEquals($result , $ExtJSdata['fields']); 
	}*/

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

