<?php
/**
 * BanchaRemotableBehaviorTest file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Model
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::uses('AppModel', 'Model');
require_once (dirname(__FILE__) . DS . 'testmodels.php'); //here we get the TestModel

App::uses('BanchaRemotableBehavior', 'Bancha.Model/Behavior');
App::uses('TreeBehavior', 'Model/Behavior');

/**
 * Exposed protected methods from ExposedMethodsBanchaRemotable for unit testing.
 */
class ExposedMethodsBanchaRemotable extends BanchaRemotableBehavior {

/**
 * A public function for the protected _getValidationRulesForField.
 * 
 * @param string $fieldName The field name of the model
 * @param array  $rules     The CakePHP rules for the field
 * @return array            The generated Sencha Touch/Ext JS validation rules
 */
	public function getValidationRulesForField($fieldName, $rules) {
		return $this->_getValidationRulesForField($fieldName, $rules);
	}

/**
 * A public function for the protected _normalizeValidationRules.
 * 
 * @param array $rules The CakePHP validation rules to normalize
 * @return array        The normalized CakePHP validation rules
 */
	public function normalizeValidationRules($rules) {
		return $this->_normalizeValidationRules($rules);
	}

}

/**
 * Used to test the TreeBehavior
 */
class RemotableTestTreeModel extends AppModel {

	public $actsAs = array('Tree');
}

/**
 * Used to test the TreeBehavior
 */
class RemotableTestTreeModelWithSpecialParentId extends AppModel {

	public $actsAs = array('Tree' => array('parent' => 'mother_id'));
}

/**
 * BanchaRemotableBehaviorTest class
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 *
 */
class BanchaRemotableBehaviorTest extends CakeTestCase {

	public $fixtures = array(
		'plugin.bancha.article',
		'plugin.bancha.article_for_testing_save_behavior',
		'plugin.bancha.user',
		'plugin.bancha.user_for_testing_last_save_result',
		'plugin.bancha.tag',
	);

	protected $_standardDebugLevel;

/**
 * Sets the plugins folder for this test
 *
 * @return void
 */
	public function setUp() {
		//App::build(array('plugins' => array( 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS ), true));
		//App::objects('plugins', null, false);
		App::build(array(
			'Model/Behavior' => array(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Model' . DS . 'Behavior' . DS)),
			App::RESET
		);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		ClassRegistry::flush();
	}

/**
 * Test that BanchaRemotable detects a variety of wrong configurations and
 * throws according errors.
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @return void
 * @dataProvider testSetupDataProvider
 * @expectedException CakeException
 * @expectedExceptionMessage Bancha: 
 */
	public function testSetup($behaviorConfig) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);
	}

/**
 * Data Provider for testSetup
 * 
 * @return array
 */
	public function testSetupDataProvider() {
		return array(
			array(
				array('exposedFields' => array()), // nothing exposed
			),
			array(
				array('exposedFields' => false), // false type
			),
			array(
				array('excludedFields' => false), // false type
			),
			array(
				array('excludeFields' => array('title')), // config typo
			),
			array(
				array('exposeFields' => array('id')), // config typo
			),
		);
	}

/**
 * Test if the internally used getExposedFields method always
 * calculates the correct set of fields to expose.
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @param array $expecedResult  The expected result
 * @return void
 * @dataProvider getExposedFieldsDataProvider
 */
	public function testGetExposedFields($behaviorConfig, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->getExposedFields($TestModel);
		$this->assertEquals($result, $expecedResult);
	}

/**
 * Data Provider for testGetExposedFields
 * 
 * @return array
 */
	public function getExposedFieldsDataProvider() {
		return array(
			array(
				array(), // default config
				array('id', 'title', 'date', 'body', 'published', 'user_id', 'headline')
			),
			array(
				array('excludedFields' => null),
				array('id', 'title', 'date', 'body', 'published', 'user_id', 'headline')
			),
			array(
				array('excludedFields' => array('body')),
				array('id', 'title', 'date', 'published', 'user_id', 'headline')
			),
			array(
				array('excludedFields' => array('headline')), // hide a virtual field
				array('id', 'title', 'date', 'body', 'published', 'user_id')
			),
			array(
				array('exposedFields' => array('id', 'date', 'body')),
				array('id', 'date', 'body')
			),
			array(
				array('exposedFields' => array('id', 'date', 'body', 'published'), 'excludedFields' => array()),
				array('id', 'date', 'body', 'published')
			),
			array(
				array('exposedFields' => array('id', 'date', 'body', 'published'), 'excludedFields' => array('published')),
				array('id', 'date', 'body')
			)
		);
	}

/**
 * Test if the internally used getExposedFields method
 * throws exceptions if misconfigured in debug mode
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @return void
 * @dataProvider getExposedFieldsExceptionsDataProvider
 * @expectedException CakeException
 */
	public function testGetExposedFieldsExceptions($behaviorConfig) {
		// turn debug mode on
		$this->_standardDebugLevel = Configure::read('debug');
		Configure::write('debug', 2);

		// run test
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		// trigger exception
		$result = $TestModel->Behaviors->BanchaRemotable->getExposedFields($TestModel);

		// turn debug mode back to default
		Configure::write('debug', $this->_standardDebugLevel);
	}

/**
 * Data Provider for testGetExposedFieldsExceptions
 * The field names do not exist
 * 
 * @return void
 */
	public function getExposedFieldsExceptionsDataProvider() {
		return array(
			array(
				array('exposedFields' => array('id', 'imaginary')),
			),
			array(
				array('exposedFields' => array('id', 'date', 'imaginary', 'body')),
			),
			array(
				array('excludedFields' => array('id', 'imaginary')),
			),
			array(
				array('excludedFields' => array('id', 'date', 'imaginary', 'body')),
			),
			array(
				array(
					'exposedFields' => array('date', 'imaginary', 'body'),
					'excludedFields' => array('date', 'imaginary', 'body')),
			),
		);
	}

/**
 * Test that isExposedField already returns the corret value
 * from given behvor config.
 *
 * @param array   $behaviorConfig The config for the BanchaRemotableBehavior
 * @param string  $fieldName      The fieldname to use
 * @param boolean $expecedResult  The expected result
 * @return void
 * @dataProvider isExposedFieldDataProvider
 */
	public function testIsExposedField($behaviorConfig, $fieldName, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->isExposedField($TestModel, $fieldName);
		$this->assertEquals($result, $expecedResult);
	}

/**
 * Data Provider for testIsExposedField
 * 
 * @return array
 */
	public function isExposedFieldDataProvider() {
		return array(
			array(
				array(),
				'title',
				true
			),
			array(
				array(),
				'headline', // virtual field
				true
			),
			array(
				array('excludedFields' => array('title', 'body')),
				'title',
				false
			),
			array(
				array('exposedFields' => array('id', 'date', 'body')),
				'title',
				false
			)
		);
	}

/**
 * Test that filterRecord filters all non-exposed fields
 * from an indivudal record.
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @param array $input          The input to use for filtering
 * @param array $expecedResult  The expected result
 * @return void
 * @dataProvider filterRecordDataProvider
 */
	public function testFilterRecord($behaviorConfig, $input, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->filterRecord($TestModel, $input);
		$this->assertEquals($result, $expecedResult);
	}

/**
 * Data Provider for testFilterRecord
 * 
 * @return array
 */
	public function filterRecordDataProvider() {
		return array(
			array(
				array(), // input==output
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2
				),
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2
				)
			),
			array(
				// hide some fields
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2
				),
				array(
					'id' => 988,
					'date' => '2011-11-24 03:40:04',
					'body' => 'Text 1',
				)
			),
			array(
				// hide some fields, make fields are not present input
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2
				),
				array(
					'body' => 'Text 1',
				)
			),
			array(
				array(), // preserve associated models
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2,
					'Article' => array('id' => 1, 'title' => 'Title 1'), // this association doesn't exist, remove it
					'User' => array('id' => 1, 'name' => 'User 1'),
					'Tag' => array(
						array('id' => 1, 'string' => 'Tag 1'),
						array('id' => 2, 'string' => 'Tag 2'),
					)
				),
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2,
					'User' => array('id' => 1, 'name' => 'User 1'),
					'Tag' => array(
						array('id' => 1, 'string' => 'Tag 1'),
						array('id' => 2, 'string' => 'Tag 2'),
					)
				)
			),
			array(
				array('exposedFields' => array('id', 'date', 'body')), // hide User association
				array(
					'id' => 988,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'headline' => '2011-11-24 03:40:04 Title 1',
					'body' => 'Text 1',
					'published' => true,
					'user_id' => 2,
					'Article' => array('id' => 1, 'title' => 'Title 1'), // this association doesn't exist, remove it
					'User' => array('id' => 1, 'name' => 'User 1'),
					'Tag' => array(
						array('id' => 1, 'string' => 'Tag 1'),
						array('id' => 2, 'string' => 'Tag 2'),
					)
				),
				array(
					'id' => 988,
					'date' => '2011-11-24 03:40:04',
					'body' => 'Text 1',
					'Tag' => array(
						array('id' => 1, 'string' => 'Tag 1'),
						array('id' => 2, 'string' => 'Tag 2'),
					)
				)
			),
		);
	}

/**
 * Test that filterRecord filters all non-associated records
 * 
 * @return void
 */
	public function testFilterRecordAssociatedRecords() {
		// prepare
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());
		$input = array(
			'id' => 988,
			'title' => 'Title 1',
			'date' => '2011-11-24 03:40:04',
			'headline' => '2011-11-24 03:40:04 Title 1',
			'body' => 'Text 1',
			'published' => true,
			'user_id' => 2,
			'User' => array('id' => 1, 'name' => 'User 1'),
			'Tag' => array(
				array('id' => 1, 'string' => 'Tag 1'),
				array('id' => 2, 'string' => 'Tag 2'),
			)
		);

		// test 1
		$expecedResult = array(
			'id' => 988,
			'title' => 'Title 1',
			'date' => '2011-11-24 03:40:04',
			'headline' => '2011-11-24 03:40:04 Title 1',
			'body' => 'Text 1',
			'published' => true,
			'user_id' => 2,
			'Tag' => array(
				array('id' => 1, 'string' => 'Tag 1'),
				array('id' => 2, 'string' => 'Tag 2'),
			)
		);
		$TestModel->unbindModel(array(
			'belongsTo' => array('User'),
		));
		$result = $TestModel->Behaviors->BanchaRemotable->filterRecord($TestModel, $input);
		$this->assertEquals($result, $expecedResult);

		// test 2
		$expecedResult = array(
			'id' => 988,
			'title' => 'Title 1',
			'date' => '2011-11-24 03:40:04',
			'headline' => '2011-11-24 03:40:04 Title 1',
			'body' => 'Text 1',
			'published' => true,
			'user_id' => 2,
		);
		$TestModel->unbindModel(
			array('hasAndBelongsToMany' => array('Tag'))
		);
		$result = $TestModel->Behaviors->BanchaRemotable->filterRecord($TestModel, $input);
		$this->assertEquals($result, $expecedResult);
	}

/**
 * Test that integer values are transformed correctly.
 *
 * This is needed if form panels load data which is not
 * build up as a record and therefore association keys
 * are not converted to integers.
 *
 * Simple Ext JS usage for this:
 *
 *     Ext.create('Ext.form.Panel', {
 *         scaffold: {
 *             target: 'Bancha.model.Article',
 *             loadRecord: 11
 *         },
 *         renderTo: 'content'
 *     });
 * 
 * @return void
 */
	public function testFilterRecordIntegerIdFields() {
		// setup
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$input = array(
			'id' => '4', //int
			'user_id' => '2', //int
			'title' => '43.5', //double
			'body' => 'Text 1', //string
		);
		$expecedResult = array(
			'id' => 4,
			'user_id' => 2,
			'title' => '43.5',
			'body' => 'Text 1',
		);
		$result = $TestModel->Behaviors->BanchaRemotable->filterRecord($TestModel, $input);
		$this->assertEquals($result, $expecedResult);
	}

/**
 * Get all associations in Ext JS/Sencha Touch format
 * 
 * @return void
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
		$this->assertEqual(count($result), 3, 'Expected three associations, instead got ' . count($result));
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Get all associations in Ext JS/Sencha Touch format.
 * Filter out belongTo associations of non-exposed fields.
 * 
 * @return void
 */
	public function testGetAssociatedFiltered() {
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
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array('excludedFields' => array('user_id')));

		$result = $TestModel->Behaviors->BanchaRemotable->getAssociated($TestModel);
		$this->assertEqual(count($result), 2, 'Expected three associations, instead got ' . count($result));
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Get all sorters in Ext JS/Sencha Touch format.
 *
 * @param array $rules         The rules to use for testing
 * @param array $expecedResult The expected result
 * @return void
 * @dataProvider getSorterDataProvider
 */
	public function testGetSorter($rules, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array('excludedFields' => array('user_id')));

		// prepare sort rule
		$TestModel->order = $rules;

		$result = $TestModel->Behaviors->BanchaRemotable->getSorters($TestModel);
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Data Provider for testGetSorter
 * See http://book.cakephp.org/2.0/en/models/model-attributes.html#order
 * 
 * @return array
 */
	public function getSorterDataProvider() {
		return array(
			array(
				"title",
				array(
					array('property' => 'title', 'direction' => 'ASC')
				)
			),
			array(
				"TestArticle.title",
				array(
					array('property' => 'title', 'direction' => 'ASC')
				)
			),
			array(
				"TestArticle.title asc",
				array(
					array('property' => 'title', 'direction' => 'ASC')
				)
			),
			array(
				"TestArticle.title ASC",
				array(
					array('property' => 'title', 'direction' => 'ASC')
				)
			),
			array(
				"TestArticle.title DESC",
				array(
					array('property' => 'title', 'direction' => 'DESC')
				)
			),
			array(
				array('TestArticle.title' => 'DESC'),
				array(
					array('property' => 'title', 'direction' => 'DESC')
				)
			),
			array(
				array('TestArticle.title' => 'ASC', 'TestArticle.body' => 'DESC'),
				array(
					array('property' => 'title', 'direction' => 'ASC'),
					array('property' => 'body', 'direction' => 'DESC')
				)
			)
		);
	}

/**
 * Test that getColumnType returns the correct column definitions in
 * Ext JS/Sencha Touch format for each CakePHP format
 *
 * @param array $cakeFieldConfig     Configuration for testing
 * @param array $expecedSenchaConfig The expected result
 * @return void
 * @dataProvider getColumnTypeDataProvider
 */
	public function testGetColumnType($cakeFieldConfig, $expecedSenchaConfig) {
		// prepare
		$banchaRemotable = new BanchaRemotableBehavior();

		// test
		$result = $banchaRemotable->getColumnType($this->getMock('Model'), 'title', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);
	}

/**
 * Data Provider for testGetColumnTypes
 * 
 * @return array
 */
	public function getColumnTypeDataProvider() {
		return array(
			array( // test default value and allowNull, as well as type string
				array(
					'type' => 'string',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'string',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test default value and allowNull, and type text
				array(
					'type' => 'text',
					'null' => false,
					'default' => 'abc'
				),
				array(
					'type' => 'string',
					'allowNull' => false,
					'defaultValue' => 'abc',
					'name' => 'title',
				),
			),
			array( // test that default value SQL empty string is transformed
				array(
					'type' => 'text',
					'null' => false,
					'default' => '""'
				),
				array(
					'type' => 'string',
					'allowNull' => false,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type integer, and different null value
				array(
					'type' => 'integer',
					'null' => '1',
					'default' => ''
				),
				array(
					'type' => 'int',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type float
				array(
					'type' => 'float',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'float',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type boolean
				array(
					'type' => 'boolean',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'boolean',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type datetime
				array(
					'type' => 'datetime',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'date',
					'dateFormat' => 'Y-m-d H:i:s',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type date
				array(
					'type' => 'date',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'date',
					'dateFormat' => 'Y-m-d',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type time
				array(
					'type' => 'time',
					'null' => true,
					'default' => ''
				),
				array(
					'type' => 'date',
					'dateFormat' => 'H:i:s',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
			array( // test type timestamp (incl. MySQL CURRENT_TIMESTAMP)
				array(
					'type' => 'timestamp',
					'null' => false,
					'default' => 'CURRENT_TIMESTAMP'
				),
				array(
					'type' => 'date',
					'dateFormat' => 'timestamp',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
		);
	}

/**
 * Test MySQL enum format
 * 
 * @return void
 */
	public function testGetColumnTypeEnum() {
		// prepare
		$model = $this->getMock('Model');
		$banchaRemotable = new BanchaRemotableBehavior();
		$cakeFieldConfig = array(
			'type' => "enum('one', 'two', 'three')",
			'null' => true,
			'default' => ''
		);
		$expecedSenchaConfig = array(
			'type' => 'string',
			'allowNull' => true,
			'defaultValue' => '',
			'name' => 'title',
		);

		// test
		$result = $banchaRemotable->getColumnType($model, 'title', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);

		// also expect a new validation rule to be set
		$expected = array(
			'inList' => array(
				'rule' => array('inList', array('one', 'two', 'three'))
			)
		);
		$this->assertEqual($expected, $model->validate['title']);
	}

/**
 * If a model used the TreeBehavior, then the parent_id should be mapped 
 * to the Ext JS parentId field.
 * 
 * @return void
 */
	public function testGetColumnTypeTreeParentId() {
		// prepare
		$banchaRemotable = new BanchaRemotableBehavior();
		$cakeFieldConfig = array(
			'type' => 'string',
			'null' => true,
			'default' => null
		);

		// test that nothing happens without the tree behavior attached
		$expecedSenchaConfig = array(
			'type' => 'string',
			'allowNull' => true,
			'defaultValue' => '',
			'name' => 'parent_id',
		);
		$result = $banchaRemotable->getColumnType($this->getMock('Model'), 'parent_id', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);

		// add the tree behavior and now expect transformed column
		$expecedSenchaConfig = array(
			'name' => 'parentId',
			'mapping' => 'parent_id',
			'type' => 'auto',
			'allowNull' => true,
			'defaultValue' => null
		);
		$result = $banchaRemotable->getColumnType(new RemotableTestTreeModel(), 'parent_id', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);

		// test with non-default parent id
		$model = new RemotableTestTreeModelWithSpecialParentId();

		// test that default parent_id is not transformed
		$expecedSenchaConfig = array(
			'type' => 'string',
			'allowNull' => true,
			'defaultValue' => '',
			'name' => 'parent_id',
		);
		$result = $banchaRemotable->getColumnType($model, 'parent_id', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);

		// test that special parent id is transformed
		$expecedSenchaConfig = array(
			'name' => 'parentId',
			'mapping' => 'mother_id',
			'type' => 'auto',
			'allowNull' => true,
			'defaultValue' => null
		);
		$result = $banchaRemotable->getColumnType($model, 'mother_id', $cakeFieldConfig);
		$this->assertEqual($result, $expecedSenchaConfig);
	}

/**
 * Test that _getColumnTypes returns all column definitions in
 * Ext JS/Sencha Touch format
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @param array $expecedResult  The expected result
 * @return void
 * @dataProvider getColumnTypesDataProvider
 */
	public function testGetColumnTypes($behaviorConfig, $expecedResult) {
		// prepare
		$TestModel = new TestArticle();
		$BanchaRemotable = new ExposedMethodsBanchaRemotable();
		$BanchaRemotable->setup($TestModel, $behaviorConfig);

		// test
		$result = $BanchaRemotable->getColumnTypes($TestModel);
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Data Provider for testGetColumnTypes
 * 
 * @return array
 */
	public function getColumnTypesDataProvider() {
		return array(
			array(
				array(), // default config
				array( // all fields
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
						'allowNull' => true,
						'defaultValue' => false,
						'type' => 'boolean',
					),
					array(
						'name' => 'user_id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
					array( // virtual field
						'name' => 'headline',
						'type' => 'auto',
						'persist' => false,
					),
				)
			),
			array(
				array('excludedFields' => array('body')),
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
						'allowNull' => true,
						'defaultValue' => false,
						'type' => 'boolean',
					),
					array(
						'name' => 'user_id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
					array( // virtual field
						'name' => 'headline',
						'type' => 'auto',
						'persist' => false,
					),
				)
			),
			array(
				array('excludedFields' => array('headline')), // virtual field
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
						'allowNull' => true,
						'defaultValue' => false,
						'type' => 'boolean',
					),
					array(
						'name' => 'user_id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
				)
			),
			array(
				array('exposedFields' => array('id', 'body')),
				array(
					array(
						'name' => 'id',
						'allowNull' => '',
						'defaultValue' => '',
						'type' => 'int',
					),
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
 * Test that normalizeValidationRules returns a normalized
 * array to process validation rules
 *
 * @param array $input         The input to normalize
 * @param array $expecedResult The expected result
 * @return void
 * @dataProvider normalizeValidationRulesDataProvider
 */
	public function testNormalizeValidationRules($input, $expecedResult) {
		// prepare
		$BanchaRemotable = new ExposedMethodsBanchaRemotable();

		// test
		$result = $BanchaRemotable->normalizeValidationRules($input);
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Data Provider for testNormalizeValidationRules
 * 
 * @return array
 */
	public function normalizeValidationRulesDataProvider() {
		return array(
			array(
				// Simple Rules
				// See http://book.cakephp.org/2.0/en/models/data-validation.html#simple-rules
				array(
					'login' => 'alphaNumeric',
					'email' => 'email',
				),
				array(
					'login' => array(
						'alphaNumeric' => array(
							'rule' => array('alphaNumeric'), // this should always be an array
						),
					),
					'email' => array(
						'email' => array(
							'rule' => array('email'), // this should always be an array
						),
					),
				),
			),
			array(
				// One Rule Per Field
				// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
				array(
					'id' => array(
						'rule' => 'numeric', // rule as string
						'precision' => 0,
						'required' => true,
						'allowEmpty' => true,
					),
					'avatar' => array(
						'rule' => array('minLength', 8),
						'required' => true,
						'allowEmpty' => false,
					),
				),
				array(
					'id' => array(
						'numeric' => array(
							'rule' => array('numeric'), // this should always be an array
							'precision' => 0,
							'required' => true,
							'allowEmpty' => true,
						),
					),
					'avatar' => array(
						'minLength' => array(
							'rule' => array('minLength', 8),
							'required' => true,
							'allowEmpty' => false,
						),
					),
				),
			),
			array(
				// Simple Rules and One Rule Per Field mixed
				array(
					'id' => array(
						'rule' => 'numeric', // rule as string
						'precision' => 0,
						'required' => true,
						'allowEmpty' => true,
					),
					'login' => 'alphaNumeric',
					'avatar' => array(
						'rule' => array('minLength', 8),
						'required' => true,
						'allowEmpty' => false,
					),
				),
				array(
					'id' => array(
						'numeric' => array(
							'rule' => array('numeric'), // this should always be an array
							'precision' => 0,
							'required' => true,
							'allowEmpty' => true,
						),
					),
					'login' => array(
						'alphaNumeric' => array(
							'rule' => array('alphaNumeric'),
						),
					),
					'avatar' => array(
						'minLength' => array(
							'rule' => array('minLength', 8),
							'required' => true,
							'allowEmpty' => false,
						),
					),
				),
			),
			array(
				// Multiple Rules Per Field
				array(
					'login' => array(
						'isUnique' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'alphaNumeric' => array(
							'rule' => 'alphaNumeric',
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
						'between' => array(
							'rule' => array('between', 5, 15),
							'message' => 'Between 5 to 15 characters'
						)
					),
				),
				array(
					'login' => array(
						'isUnique' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'alphaNumeric' => array(
							'rule' => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
						'between' => array(
							'rule' => array('between', 5, 15),
							'message' => 'Between 5 to 15 characters'
						)
					),
				),
			),
			array(
				// Multiple Rules Per Field - arbitrary rule names
				array(
					'login' => array(
						'loginRule-1' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'loginRule-2' => array(
							'rule' => 'alphaNumeric',
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
					),
				),
				array(
					'login' => array(
						'isUnique' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'alphaNumeric' => array(
							'rule' => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
					),
				),
			),
			array(
				// All together
				array(
					'id' => array( // multiple rules
						'rule' => 'numeric', // rule as string
						'precision' => 0,
						'required' => true,
						'allowEmpty' => true,
					),
					'login' => array(
						'loginRule-1' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'loginRule-2' => array(
							'rule' => 'alphaNumeric',
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
					),
					'email' => 'email', // simple rule
					'avatar' => array( // one rule per field
						'rule' => array('minLength', 8),
						'required' => true,
						'allowEmpty' => false,
					),
				),
				array(
					'id' => array(
						'numeric' => array(
							'rule' => array('numeric'), // this should always be an array
							'precision' => 0,
							'required' => true,
							'allowEmpty' => true,
						),
					),
					'login' => array(
						'isUnique' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'alphaNumeric' => array(
							'rule' => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message' => 'Alphabets and numbers only'
						),
					),
					'email' => array(
						'email' => array(
							'rule' => array('email'), // this should always be an array
						),
					),
					'avatar' => array(
						'minLength' => array(
							'rule' => array('minLength', 8),
							'required' => true,
							'allowEmpty' => false,
						),
					),
				),
			),
		);
	}

/**
 * Test that getValidationRulesForField returns all validation definitions
 * for one model field in Ext JS/Sencha Touch format
 *
 * @param string $fieldName     The field name to use for testing
 * @param array $rules         The rules to use for testing
 * @param array $expecedResult The expected result
 * @return void
 * @dataProvider getValidationRulesForFieldDataProvider
 */
	public function testGetValidationRulesForField($fieldName, $rules, $expecedResult) {
		// prepare
		$TestModel = new TestArticle();
		$BanchaRemotable = new ExposedMethodsBanchaRemotable();
		$BanchaRemotable->setup($TestModel, array());

		// test
		$result = $BanchaRemotable->getValidationRulesForField($fieldName, $rules);
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Data Provider for testGetValidationRulesForField
 * 
 * @return array
 */
	public function getValidationRulesForFieldDataProvider() {
		return array(
			// test cake alphaNumeric rule
			array(
				'login',
				array(
					'alphaNumeric' => array(
						'rule' => array('alphaNumeric'),
					),
				),
				array(
					array(
						'type' => 'format',
						'field' => 'login',
						'matcher' => 'banchaAlphanum'
					),
				),
			),
			// test cake alphaNumeric rule
			array(
				'login',
				array(
					'between' => array(
						'rule' => array('between', 0, 15),
						'message' => 'Between 0 to 15 characters'
					)
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'min' => 0,
						'max' => 15,
					),
				),
			),
			array(
				'login',
				array(
					'between' => array(
						'rule' => array('between', 5, 23),
						'message' => 'Between 5 to 23 characters'
					)
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'min' => 5,
						'max' => 23,
					),
				),
			),
			// test cake boolean rule
			array(
				'published',
				array(
					'boolean' => array(
						'rule' => array('boolean'),
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'published',
						'list' => array(true, false, '0', '1', 0, 1),
					),
				),
			),
			// test cake equalTo rule
			array(
				'published',
				array(
					'equalTo' => array(
						'rule' => array('equalTo', 'bancha'),
						'message' => 'This value must be the string bancha'
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'published',
						'list' => array('bancha'),
					),
				),
			),
			// test cake extension rule
			array(
				'avatar',
				array(
					'extension' => array(
						'rule' => array('extension', array('gif', 'jpeg', 'png', 'jpg')),
						'message' => 'Please supply a valid image.'
					),
				),
				array(
					array(
						'type' => 'file',
						'field' => 'avatar',
						'extension' => array('gif', 'jpeg', 'png', 'jpg'),
					),
				),
			),
			array(
				'avatar',
				array(
					'extension' => array(
						'rule' => array('extension', array('php')),
						'message' => 'Please supply a php file.'
					),
				),
				array(
					array(
						'type' => 'file',
						'field' => 'avatar',
						'extension' => array('php'),
					),
				),
			),
			// test cake inList rule
			array(
				'name',
				array(
					'inList' => array(
						'rule' => array('inList', array('a', 'ab')),
						'message' => 'This file can only be "a" or "ab" or undefined.'
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'name',
						'list' => array('a', 'ab'),
					),
				),
			),
			// test cake inList rule
			array(
				'name',
				array(
					'inList' => array(
						'rule' => array('inList', array('a', 'ab')),
						'message' => 'This file can only be "a" or "ab" or undefined.'
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'name',
						'list' => array('a', 'ab'),
					),
				),
			),
			// test cake minLength, maxLength
			// aka: test Ext JS/Sencha Touch length rule (see also between)
			array(
				'login',
				array(
					'minLength' => array(
						'rule' => array('minLength', 3),
						'message' => 'Usernames must be nat least 3 characters long.',
					),
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'min' => 3,
					),
				),
			),
			array(
				'login',
				array(
					'maxLength' => array(
						'rule' => array('maxLength', 15),
						'message' => 'Usernames must be no larger than 15 characters long.',
					),
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'max' => 15,
					),
				),
			),
			array(
				'login',
				array(
					// min and max should be combined
					'minLength' => array(
						'rule' => array('minLength', 3),
						'message' => 'Usernames must be at least 3 characters long.',
					),
					'maxLength' => array(
						'rule' => array('maxLength', 15),
						'message' => 'Usernames must be no larger than 15 characters long.',
					),
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'min' => 3,
						'max' => 15,
					),
				),
			),
			array(
				'login',
				array(
					// min and max should be combined
					'minLength' => array(
						'rule' => array('minLength', 3),
						'message' => 'Usernames must be at least 3 characters long.',
					),
					'maxLength' => array(
						'rule' => array('maxLength', 15),
						'message' => 'Usernames must be no larger than 15 characters long.',
					),
				),
				array(
					array(
						'type' => 'length',
						'field' => 'login',
						'min' => 3,
						'max' => 15,
					),
				),
			),
			// test cake numeric rule
			array(
				'weight',
				array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => 'Weight must be a number.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
					),
				),
			),
			array(
				'weight',
				array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => 'Weight must be a number.',
						'precision' => 0,
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 0,
					),
				),
			),
			// test cake naturalNumber rule
			array(
				'weight',
				array(
					'naturalNumber' => array(
						'rule' => array('naturalNumber'),
						'message' => 'Weight must be a natural number greater zero.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 0,
						'min' => 1,
					),
				),
			),
			array(
				'weight',
				array(
					'naturalNumber' => array(
						'rule' => array('naturalNumber', true),
						'message' => 'Weight must be a natural number or zero.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 0,
						'min' => 0,
					),
				),
			),
			// test cake range rule
			array(
				'weight',
				array(
					'numeric' => array(
						'rule' => array('numeric'),
						'precision' => 0,
					),
					'range' => array(
						'rule' => array('range', -1, 301),
						'message' => 'Weight must be a number between 0 and 300, including 0 and 300.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 0,
						'min' => 0,
						'max' => 300
					),
				),
			),
			array(
				'weight',
				array(
					'numeric' => array(
						'rule' => array('numeric'),
						'precision' => 1, // test handling of precision
					),
					'range' => array(
						'rule' => array('range', 0, 300.5), // test handling of decimals
						'message' => 'Weight must be a number between 1 and 300.5, including those.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 1,
						'min' => 0.1,
						'max' => 300.4
					),
				),
			),
			array(
				'weight',
				array(
					'numeric' => array(
						'rule' => array('numeric'),
						'precision' => 3, // test handling of precision
					),
					'range' => array(
						'rule' => array('range', 0, 300.5), // test handling of decimals
						'message' => 'Weight must be a number between 1 and 300.5, including those.',
					),
				),
				array(
					array(
						'type' => 'range',
						'field' => 'weight',
						'precision' => 3,
						'min' => 0.001,
						'max' => 300.499
					),
				),
			),
			// test cake numeric rule
			array(
				'url_field',
				array(
					'url' => array(
						'rule' => array('url'),
						'message' => 'Url must be a valid url',
					),
				),
				array(
					array(
						'type' => 'format',
						'field' => 'url_field',
						'matcher' => 'banchaUrl',
					),
				),
			),
			// test cake require and allowEmpty property, as well as notEmpty rule
			// aka: test Ext JS/Sencha Touch presence rule
			array(
				'login',
				array(
					'isUnique' => array(
						'rule' => array('isUnique'),
					),
					'alphaNumeric' => array(
						'rule' => array('alphaNumeric'),
						'required' => true, // <-- via require
					),
				),
				array(
					array(
						'type' => 'presence',
						'field' => 'login',
					),
					array(
						'type' => 'format',
						'field' => 'login',
						'matcher' => 'banchaAlphanum'
					),
				),
			),
			array(
				'login',
				array(
					'isUnique' => array(
						'rule' => array('isUnique'),
					),
					'alphaNumeric' => array(
						'rule' => array('alphaNumeric'),
						'allowEmpty' => false, // <-- via allowEmpty
					),
				),
				array(
					array(
						'type' => 'presence',
						'field' => 'login',
					),
					array(
						'type' => 'format',
						'field' => 'login',
						'matcher' => 'banchaAlphanum'
					),
				),
			),
			array(
				'login',
				array(
					'notEmpty' => array(
						'rule' => array('notEmpty'), // via notEmpty
					),
				),
				array(
					array(
						'type' => 'presence',
						'field' => 'login',
					),
				),
			),
		);
	}

/**
 * Test that getValidationRulesForField returns all validation definitions
 * for one model field in Ext JS/Sencha Touch format
 *
 * @param string $fieldName The field name to use for testing
 * @param array $rules      The rules to use for testing
 * @return void
 * @dataProvider getValidationRulesForFieldDataProviderExceptions
 */
	public function testGetValidationRulesForFieldExceptions($fieldName, $rules) {
		// turn debug mode on
		$this->_standardDebugLevel = Configure::read('debug');
		Configure::write('debug', 2);

		// run test
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		// trigger exception
		$result = $TestModel->Behaviors->BanchaRemotable->getExposedFields($TestModel);

		// turn debug mode back to default
		Configure::write('debug', $this->_standardDebugLevel);
	}

/**
 * Data Provider for testGetValidationRulesForFieldExceptions
 * 
 * @return array
 */
	public function getValidationRulesForFieldDataProviderExceptions() {
		return array(
			// range requires a precision
			array(
				'weight',
				array(
					'range' => array(
						'rule' => array('range', 0, 300),
						'message' => 'Weight must be a number between 1 and 300, including those.',
					),
				),
			),
		);
	}

/**
 * Test that getValidations returns all validation definitions in
 * Ext JS/Sencha Touch format.
 *
 * This is only an integration test, the individual rules are tested above
 *
 * @param string $TestModelName  The model name to use for testing
 * @param array  $behaviorConfig The config for the BanchaRemotableBehavior
 * @param array  $expecedResult  The expected result
 * @return void
 * @dataProvider getValidationsDataProvider
 */
	public function testGetValidations($TestModelName, $behaviorConfig, $expecedResult) {
		$TestModel = new $TestModelName();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->getValidations($TestModel);
		//pr($result); exit();
		$this->assertEqual($result, $expecedResult);
	}

/**
 * Data Provider for testGetValidations
 * 
 * @return array
 */
	public function getValidationsDataProvider() {
		return array(
			array(
				'TestUser', // Simple and One Rule Per Field Rules
				array(), // default behavior config
				array( // expect all rules
					array(
						'type' => 'format',
						'field' => 'login',
						'matcher' => 'banchaAlphanum'
					),
					array(
						'type' => 'format',
						'field' => 'email',
						'matcher' => 'banchaEmail',
					),
					array(
						'type' => 'presence',
						'field' => 'id',
					),
					array(
						'type' => 'range',
						'field' => 'id',
						'precision' => 0
					),
					array(
						'type' => 'presence',
						'field' => 'avatar',
					),
					array(
						'type' => 'length',
						'field' => 'avatar',
						'min' => '8'
					),
				),
			),
			array(
				'TestUserCoreValidationRules', // Test core validation rules
				array(), // default behavior config
				array( // expect all rules
					array(
						'type' => 'range', // from naturalNumber
						'field' => 'id'
					),
					array(
						'type' => 'presence', // from required true
						'field' => 'login',
					),
					array(
						'type' => 'length', // from between
						'field' => 'login',
						'min' => 5,
						'max' => 15,
					),
					array(
						'type' => 'format', // from alphaNumeric
						'field' => 'login',
						'matcher' => 'banchaAlphanum',
					),
					array(
						'type' => 'inclusion', // from boolean
						'field' => 'published',
						'list' => array(true, false, '0', '1', 0, 1),
					),
					array(
						'type' => 'presence', // from notEmpty
						'field' => 'name',
					),
					array(
						'type' => 'length', // from minLength and maxLength
						'field' => 'name',
						'min' => 3,
						'max' => 64,
					),
					array(
						'type' => 'format', // from email
						'field' => 'email',
						'matcher' => 'banchaEmail',
					),
					array(
						'type' => 'file', // from extension
						'field' => 'avatar',
						'extension' => array('gif', 'jpeg', 'png', 'jpg'),
					),
					array(
						'type' => 'inclusion', // from inList
						'field' => 'a_or_ab_only',
						'list' => array('a', 'ab'),
					),
				),
			),
			array(
				'TestUser',
				array( // test filtering (only integrative test)
					'exposedFields' => array('id', 'email', 'avatar'),
					'excludedFields' => array('avatar'),
				),
				array( // expect all rules
					array(
						'type' => 'format',
						'field' => 'email',
						'matcher' => 'banchaEmail',
					),
					array(
						'type' => 'presence',
						'field' => 'id',
					),
					array(
						'type' => 'range',
						'field' => 'id',
						'precision' => 0
					),
				),
			),
		);
	}

/**
 * Test that getValidations works if no validation rules are defined
 * 
 * @return void
 */
	public function testGetValidationsNoValidationRules() {
		$TestModel = new TestArticleNoValidationRules();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$expecedResult = array(); // simply return an empty array

		$result = $TestModel->Behaviors->BanchaRemotable->getValidations($TestModel);
		$this->assertEqual($result, $expecedResult);
	}

/**
 * extractBanchaMetaData basically wraps different functions without internal logic,
 * so execute only a small integrational test.
 * 
 * @return void
 */
	public function testExtractBanchaMetaData() {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$result = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);

		// check results
		$this->assertEqual($result['idProperty'], 'id');
		$this->assertEquals($result['displayField'], 'title');
		$this->assertEqual(count($result['fields']), 7);
		$this->assertEqual(count($result['validations']), 3);
		$this->assertEqual(count($result['associations']), 3);
		$this->assertEqual(count($result['sorters']), 1);
	}

/**
 * Test that BanchaRemotable detects a variety of wrong configurations and
 * throws according errors.
 *
 * @param array $behaviorConfig The config for the BanchaRemotableBehavior
 * @return void
 * @dataProvider testExtractBanchaMetaDataErrorsDataProvider
 * @expectedException CakeException
 * @expectedExceptionMessage Bancha: 
 */
	public function testExtractBanchaMetaDataErrors($behaviorConfig) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);
		$result = $TestModel->Behaviors->BanchaRemotable->extractBanchaMetaData($TestModel);
	}

/**
 * Data Provider for testExtractBanchaMetaDataErrors
 * 
 * @return array
 */
	public function testExtractBanchaMetaDataErrorsDataProvider() {
		return array(
			array(
				array('exposedFields' => array('id', 'non-existing-field')), // non-existing field
			),
			array(
				array('exposedFields' => array('title')), // missing id
			),
			array(
				array('excludedFields' => array('id')), // missing id
			),
		);
	}

/**
 * Tests that custom validation rule swhich execute a find doesn't break bancha
 * see issue: https://github.com/Bancha/Bancha/issues/22
 * 
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

/**
 * Tests that proper validation error response is sent
 * 
 * @return void
 */
	public function testGetLastSaveResultValidationFailed() {
		$user = ClassRegistry::init('UserForTestingLastSaveResult');

		// save user
		$user->create();
		$result = $user->saveFieldsAndReturn(array(
			'UserForTestingLastSaveResult' => array(
				'name' => 'Roland',
				'login' => 'roland',
				'id' => 1
		)));
		$this->assertTrue(empty($result['success']));

		// save another user
		$user->create();
		$result = $user->saveFieldsAndReturn(array(
			'UserForTestingLastSaveResult' => array(
				'name' => 'Andrejs',
				'login' => 'andrejs',
				'id' => 2
		)));
		$this->assertTrue(empty($result['success']));

		// test unique login validation check
		$user->create();
		$result = $user->saveFieldsAndReturn(array(
			'UserForTestingLastSaveResult' => array(
				'name' => 'Roland',
				'login' => 'roland',
				'id' => 3
		)));
		$this->assertEqual($result, array(
			'success' => false,
			'errors' => array(
				'login' => 'Login is already taken.'
			)
		));

		// test validation with two rule violation
		$user->create();
		$result = $user->saveFieldsAndReturn(array(
			'UserForTestingLastSaveResult' => array(
				'name' => '',
				'login' => 'andrejs',
				'id' => 4
		)));
		$this->assertEqual($result, array(
			'success' => false,
			'errors' => array(
				'login' => 'Login is already taken.',
				'name' => 'Name is required.'
			)
		));
	}
}

