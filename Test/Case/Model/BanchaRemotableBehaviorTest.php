<?php
/**
 * BanchaRemotableBehaviorTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Model
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
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
	 * @dataProvider getExposedFieldsDataProvider
	 */
	public function testGetExposedFields($behaviorConfig, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->_getExposedFields($TestModel);
		$this->assertEquals($result, $expecedResult);
	}
	/**
	 * Data Provider for testGetExposedFields
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
	 * Test if the internally used _getExposedFields method
	 * throws exceptions if misconfigured in debug mode
	 *
	 * @dataProvider getExposedFieldsDataProvider_Exceptions
	 * @expectedException CakeException
	 */
	public function testGetExposedFields_Exceptions($behaviorConfig) {
		// turn debug mode on
		$this->standardDebugLevel = Configure::read('debug');
		Configure::write('debug', 2);

		// run test
		$TestModel = new TestArticle();
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
	public function getExposedFieldsDataProvider_Exceptions() {
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
				array('excludedFields' => array('id', 'title')),
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
		);
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
	 */
	public function testFilterRecord_IntegerIdFields() {

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
	 * Test that filterRecords filters all non-exposed fields
	 * from all possible inputs (see test provider)
	 *
	 * @dataProvider filterRecordsDataProvider
	 */
	public function testFilterRecords($behaviorConfig, $input, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->filterRecords($TestModel, $input);
		$this->assertEquals($result, $expecedResult);
	}
	/**
	 * Data Provider for testFilterRecords
	 */
	public function filterRecordsDataProvider() {
		return array(
			array(
				// find('first') structure, no filter
				array(), // input==output
				array(
					'Article' => array(
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
					'Article' => array(
						'id' => 988,
						'title' => 'Title 1',
						'date' => '2011-11-24 03:40:04',
						'headline' => '2011-11-24 03:40:04 Title 1',
						'body' => 'Text 1',
						'published' => true,
						'user_id' => 2
					)
				),
			),
			array(
				// find('first') structure, hide some fields
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					'Article' => array(
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
					'Article' => array(
						'id' => 988,
						'date' => '2011-11-24 03:40:04',
						'body' => 'Text 1',
					)
				),
			),
			array(
				// find('first') structure, partial data
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					'Article' => array(
						'body' => 'Text 1',
						'published' => true,
						'user_id' => 2
					)
				),
				array(
					'Article' => array(
						'body' => 'Text 1',
					)
				),
			),
			array(
				// find('all') structure, no filter
				array(), // input==output
				array(
					array(
						'Article' => array(
							'id' => 988,
							'title' => 'Title 1',
							'date' => '2011-11-24 03:40:04',
							'headline' => '2011-11-24 03:40:04 Title 1',
							'body' => 'Text 1',
							'published' => true,
							'user_id' => 2
						),
					),
					array(
						'Article' => array(
							'id' => 989,
							'title' => 'Title 2',
							'date' => '2011-12-24 03:40:04',
							'body' => 'Text 2',
							'published' => false,
							'user_id' => 3,
						),
					),
					array(
						'Article' => array(
							'id' => 990,
							'title' => 'Title 3',
							'date' => '2010-12-24 03:40:04',
							'body' => 'Text 3',
							'published' => false,
							'user_id' => 3,
						),
					),
				),
				array(
					array(
						'Article' => array(
							'id' => 988,
							'title' => 'Title 1',
							'date' => '2011-11-24 03:40:04',
							'headline' => '2011-11-24 03:40:04 Title 1',
							'body' => 'Text 1',
							'published' => true,
							'user_id' => 2
						),
					),
					array(
						'Article' => array(
							'id' => 989,
							'title' => 'Title 2',
							'date' => '2011-12-24 03:40:04',
							'body' => 'Text 2',
							'published' => false,
							'user_id' => 3,
						),
					),
					array(
						'Article' => array(
							'id' => 990,
							'title' => 'Title 3',
							'date' => '2010-12-24 03:40:04',
							'body' => 'Text 3',
							'published' => false,
							'user_id' => 3,
						),
					),
				),
			),
			array(
				// find('all') structure, hide some fields and partial data
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					array(
						'Article' => array(
							'title' => 'Title 1',
							'date' => '2011-11-24 03:40:04',
							'headline' => '2011-11-24 03:40:04 Title 1',
							'body' => 'Text 1',
							'published' => true,
							'user_id' => 2
						),
					),
					array(
						'Article' => array(
							'id' => 989,
							'title' => 'Title 2',
							'body' => 'Text 2',
							'published' => false,
							'user_id' => 3,
						),
					),
					array(
						'Article' => array(
							'id' => 990,
							'title' => 'Title 3',
							'date' => '2010-12-24 03:40:04',
							'body' => 'Text 3',
							'published' => false,
							'user_id' => 3,
						),
					),
				),
				array(
					array(
						'Article' => array(
							'date' => '2011-11-24 03:40:04',
							'body' => 'Text 1',
						),
					),
					array(
						'Article' => array(
							'id' => 989,
							'body' => 'Text 2',
						),
					),
					array(
						'Article' => array(
							'id' => 990,
							'date' => '2010-12-24 03:40:04',
							'body' => 'Text 3',
						),
					),
				),
			),
			array(
				// paginated data structure, hide some fields and partial data
				array('exposedFields' => array('id', 'date', 'body')),
				array(
					'count' => 100,
					'records' => array(
						array(
							'Article' => array(
								'title' => 'Title 1',
								'date' => '2011-11-24 03:40:04',
								'headline' => '2011-11-24 03:40:04 Title 1',
								'body' => 'Text 1',
								'published' => true,
								'user_id' => 2
							),
						),
						array(
							'Article' => array(
								'id' => 989,
								'title' => 'Title 2',
								'body' => 'Text 2',
								'published' => false,
								'user_id' => 3,
							),
						),
						array(
							'Article' => array(
								'id' => 990,
								'title' => 'Title 3',
								'date' => '2010-12-24 03:40:04',
								'body' => 'Text 3',
								'published' => false,
								'user_id' => 3,
							),
						),
					),
				),
				array(
					'count' => 100,
					'records' => array(
						array(
							'Article' => array(
								'date' => '2011-11-24 03:40:04',
								'body' => 'Text 1',
							),
						),
						array(
							'Article' => array(
								'id' => 989,
								'body' => 'Text 2',
							),
						),
						array(
							'Article' => array(
								'id' => 990,
								'date' => '2010-12-24 03:40:04',
								'body' => 'Text 3',
							),
						),
					),
				),
			),
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
	 *
	 * @dataProvider getSorterDataProvider
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
	 * See http://book.cakephp.org/2.0/en/models/model-attributes.html#order
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
	 * ExtJS/Sencha Touch format for each CakePHP format
	 *
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
					'dateFormat' =>'Y-m-d H:i:s',
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
					'dateFormat' =>'Y-m-d',
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
					'dateFormat' =>'H:i:s',
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
					'dateFormat' =>'timestamp',
					'allowNull' => true,
					'defaultValue' => '',
					'name' => 'title',
				),
			),
		);
	}

	/**
	 * Test MySQL enum format
	 */
	public function testGetColumnType_Enum() {

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
	 * Test that getColumnTypes returns all column definitions in
	 * ExtJS/Sencha Touch format
	 *
	 * @dataProvider getColumnTypesDataProvider
	 */
	public function testGetColumnTypes($behaviorConfig, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', $behaviorConfig);

		$result = $TestModel->Behaviors->BanchaRemotable->getColumnTypes($TestModel);
		$this->assertEqual($result, $expecedResult);
	}
	/**
	 * Data Provider for testGetColumnTypes
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
	 * @dataProvider normalizeValidationRulesDataProvider
	 */
	public function testNormalizeValidationRules($input, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$result = $TestModel->Behaviors->BanchaRemotable->normalizeValidationRules($input);
		$this->assertEqual($result, $expecedResult);
	}
	/**
	 * Data Provider for testNormalizeValidationRules
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
						'required'   => true,
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
							'required'   => true,
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
						'required'   => true,
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
							'required'   => true,
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
							'rule'	 => 'alphaNumeric',
							'required' => true,
							'message'  => 'Alphabets and numbers only'
						),
						'between' => array(
							'rule'	=> array('between', 5, 15),
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
							'rule'	 => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message'  => 'Alphabets and numbers only'
						),
						'between' => array(
							'rule'	=> array('between', 5, 15),
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
							'rule'	 => 'alphaNumeric',
							'required' => true,
							'message'  => 'Alphabets and numbers only'
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
							'rule'	 => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message'  => 'Alphabets and numbers only'
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
						'required'   => true,
						'allowEmpty' => true,
					),
					'login' => array(
						'loginRule-1' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'loginRule-2' => array(
							'rule'	 => 'alphaNumeric',
							'required' => true,
							'message'  => 'Alphabets and numbers only'
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
							'required'   => true,
							'allowEmpty' => true,
						),
					),
					'login' => array(
						'isUnique' => array(
							'rule' => array('isUnique'),
							'message' => "Login is already taken."
						),
						'alphaNumeric' => array(
							'rule'	 => array('alphaNumeric'), // this should always be an array
							'required' => true,
							'message'  => 'Alphabets and numbers only'
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
	 * for one model field in ExtJS/Sencha Touch format
	 *
	 * @dataProvider getValidationRulesForFieldDataProvider
	 */
	public function testGetValidationRulesForField($fieldName, $rules, $expecedResult) {
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$result = $TestModel->Behaviors->BanchaRemotable->getValidationRulesForField($fieldName, $rules);
		//pr($result); exit();
		$this->assertEqual($result, $expecedResult);
	}
	/**
	 * Data Provider for testGetValidationRulesForField
	 */
	public function getValidationRulesForFieldDataProvider() {
		return array(
			// test cake alphaNumeric rule
			array(
				'login',
				array(
					'alphaNumeric' => array(
						'rule'	 => array('alphaNumeric'),
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
						'rule'	=> array('between', 0, 15),
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
						'rule'	=> array('between', 5, 23),
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
						'list' => array(true,false,'0','1',0,1),
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
						'extension' => array('gif','jpeg','png','jpg'),
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
						'rule'    => array('inList', array('a', 'ab')),
						'message' => 'This file can only be "a" or "ab" or undefined.'
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'name',
						'list' => array('a','ab'),
					),
				),
			),
			// test cake inList rule
			array(
				'name',
				array(
					'inList' => array(
						'rule'    => array('inList', array('a', 'ab')),
						'message' => 'This file can only be "a" or "ab" or undefined.'
					),
				),
				array(
					array(
						'type' => 'inclusion',
						'field' => 'name',
						'list' => array('a','ab'),
					),
				),
			),
			// test cake minLength, maxLength
			// aka: test ExtJS/Sencha Touch length rule (see also between)
			array(
				'login',
				array(
					'minLength' => array(
						'rule'    => array('minLength', 3),
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
						'rule'    => array('maxLength', 15),
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
						'rule'    => array('minLength', 3),
						'message' => 'Usernames must be at least 3 characters long.',
					),
					'maxLength' => array(
						'rule'    => array('maxLength', 15),
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
						'rule'    => array('minLength', 3),
						'message' => 'Usernames must be at least 3 characters long.',
					),
					'maxLength' => array(
						'rule'    => array('maxLength', 15),
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
						'rule'    => array('numeric'),
						'message' => 'Weight must be a number.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
						'field' => 'weight',
					),
				),
			),
			array(
				'weight',
				array(
					'numeric' => array(
						'rule'    => array('numeric'),
						'message' => 'Weight must be a number.',
						'precision' => 0,
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('naturalNumber'),
						'message' => 'Weight must be a natural number greater zero.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('naturalNumber', true),
						'message' => 'Weight must be a natural number or zero.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('numeric'),
						'precision' => 0,
					),
					'range' => array(
						'rule'    => array('range', -1, 301),
						'message' => 'Weight must be a number between 0 and 300, including 0 and 300.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('numeric'),
						'precision' => 1, // test handling of precision
					),
					'range' => array(
						'rule'    => array('range', 0, 300.5), // test handling of decimals
						'message' => 'Weight must be a number between 1 and 300.5, including those.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('numeric'),
						'precision' => 3, // test handling of precision
					),
					'range' => array(
						'rule'    => array('range', 0, 300.5), // test handling of decimals
						'message' => 'Weight must be a number between 1 and 300.5, including those.',
					),
				),
				array(
					array(
						'type' => 'numberformat',
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
						'rule'    => array('url'),
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
			// aka: test ExtJS/Sencha Touch presence rule
			array(
				'login',
				array(
					'isUnique' => array(
						'rule' => array('isUnique'),
					),
					'alphaNumeric' => array(
						'rule'	 => array('alphaNumeric'),
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
						'rule'	 => array('alphaNumeric'),
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
	 * for one model field in ExtJS/Sencha Touch format
	 *
	 * @dataProvider getValidationRulesForFieldDataProvider_Exceptions
	 */
	public function testGetValidationRulesForField_Exceptions($fieldName, $rules) {
		// turn debug mode on
		$this->standardDebugLevel = Configure::read('debug');
		Configure::write('debug', 2);

		// run test
		$TestModel = new TestArticle();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		// trigger exception
		$result = $TestModel->Behaviors->BanchaRemotable->_getExposedFields($TestModel);

		// turn debug mode back to default
		Configure::write('debug', $this->standardDebugLevel);
	}
	/**
	 * Data Provider for testGetValidationRulesForField_Exceptions
	 */
	public function getValidationRulesForFieldDataProvider_Exceptions() {
		return array(
			// range requires a precision
			array(
				'weight',
				array(
					'range' => array(
						'rule'    => array('range', 0, 300),
						'message' => 'Weight must be a number between 1 and 300, including those.',
					),
				),
			),
		);
	}



	/**
	 * Test that getValidations returns all validation definitions in
	 * ExtJS/Sencha Touch format.
	 *
	 * This is only an integration test, the individual rules are tested above
	 *
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
						'type' => 'numberformat',
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
						'type' => 'numberformat', // from naturalNumber
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
						'list' => array(true,false,'0','1',0,1),
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
						'extension' => array('gif','jpeg','png','jpg'),
					),
					array(
						'type' => 'inclusion', // from inList
						'field' => 'a_or_ab_only',
						'list' => array('a','ab'),
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
						'type' => 'numberformat',
						'field' => 'id',
						'precision' => 0
					),
				),
			),
		);
	}

	/**
	 * Test that getValidations works if no validation rules are defined
	 */
	public function testGetValidations_NoValidationRules() {
		$TestModel = new TestArticleNoValidationRules();
		$TestModel->Behaviors->attach('Bancha.BanchaRemotable', array());

		$expecedResult = array(); // simply return an empty array

		$result = $TestModel->Behaviors->BanchaRemotable->getValidations($TestModel);
		$this->assertEqual($result, $expecedResult);
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
		$this->assertEqual(count($result['fields']), 7);
		$this->assertEqual(count($result['validations']), 3);
		$this->assertEqual(count($result['associations']), 3);
		$this->assertEqual(count($result['sorters']), 1);
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

