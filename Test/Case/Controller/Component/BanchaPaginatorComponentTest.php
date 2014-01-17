<?php
/**
 * BanchaPaginatorComponentTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('BanchaPaginatorComponent', 'Bancha.Controller/Component');

/**
 * TestBanchaPaginatorComponentsController
 *
 * A fake controller to test against
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.1.0
 */
class TestBanchaPaginatorComponentsController extends Controller {

	public $uses = array('Article');

	public $components = array('Session', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));

/**
 * Used in the testPaginationConditionApplying to
 * test setting the paginate via method argument
 *
 * @return void
 */
	public function getPaginationConditionsArgument() {
		$this->Article->recursive = -1;

		// directly pushed conditions are applied
		return $this->Paginator->paginate('Article', array(
			array('Article.title' => 'Title 1')
		));
	}

/**
 * Used in the testPaginationConditionApplying to
 * test setting the paginate via overloaded property
 *
 * @return void
 */
	public function getPaginationConditionsProperty() {
		$this->Article->recursive = -1;

		// indirectly set conditions are applied
		$this->paginate = array(
			'conditions' => array('Article.title' => 'Title 1')
		);

		return $this->Paginator->paginate();
	}
}

/**
 * BanchaPaginatorComponentTest
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.1.0
 */
class BanchaPaginatorComponentTest extends ControllerTestCase {

	public $fixtures = array('plugin.bancha.article');

	protected $_originalDebugLevel;

/**
 * This method creates a controller and a component with the given settings
 * 
 * @param array $settings The setting to set up the component
 * @param array $conditions The faked conditions data
 * @return void
 */
	public function setUpComponent($settings, $conditions = array()) {
		// Setup our component and fake test controller
		// See http://book.cakephp.org/2.0/en/development/testing.html#testing-components

		// setup the controller
		$CakeRequest = new CakeRequest();
		$CakeRequest->params['isBancha'] = true; // fake a Bancha request
		$CakeRequest->params['named']['conditions'] = $conditions; // this exist in every Bancha request
		$CakeResponse = new CakeResponse();
		$this->Controller = new TestBanchaPaginatorComponentsController($CakeRequest, $CakeResponse);
		$this->Controller->Article->recursive = -1; // we only load article fixture, so don't load associated data

		// setup the component collection
		$Collection = new ComponentCollection();
		$Collection->init($this->Controller);

		// setup the paginator component
		$this->BanchaPaginatorComponent = new BanchaPaginatorComponent($Collection, $settings);
		$this->BanchaPaginatorComponent->startup($this->Controller);
	}

/**
 * setUp method
 * 
 * @return void
 */
	public function setUp() {
		parent::setUp();

		/*
		App::build(array(
			'plugins' => $this->_paths['plugins'],
			'views' => $this->_paths['views'],
			'controllers' => $this->_paths['controllers'],
			'vendors' => $this->_paths['vendors']
		), true);
		*/
	
		// keep debug level
		$this->_originalDebugLevel = Configure::read('debug');
	}

/**
 * tearDown method
 * 
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->_originalDebugLevel);

		// Clean up after we're done
		unset($this->BanchaPaginatorComponent);
		unset($this->Controller);
	}

/**
 * testSetSettings
 *
 * @return void
 */
	public function testSetSettings() {
		// test setting allowed filters to all
		$this->setUpComponent(array('allowedFilters' => 'all'));
		$this->assertEquals('all', $this->BanchaPaginatorComponent->allowedFilters);

		// test setting allowed filters to associations
		$this->setUpComponent(array('allowedFilters' => 'associations'));
		$this->assertEquals('associations', $this->BanchaPaginatorComponent->allowedFilters);

		// test setting allowed filters to none
		$this->setUpComponent(array('allowedFilters' => 'none'));
		$this->assertTrue(is_array($this->BanchaPaginatorComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaPaginatorComponent->allowedFilters), 0);

		$this->setUpComponent(array('allowedFilters' => array()));
		$this->assertTrue(is_array($this->BanchaPaginatorComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaPaginatorComponent->allowedFilters), 0);

		// test setting an array of existing values
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'Article.body', 'Article.published')));
		$this->assertTrue(is_array($this->BanchaPaginatorComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaPaginatorComponent->allowedFilters), 3);
	}

/**
 * testSetSettingsDebuggingExceptionsNull
 *
 * @return void
 */
	public function testSetSettingsDebuggingExceptionsNull() {
		// test setting an to null
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponents allowedFilters configuration needs to be set.');
		$this->setUpComponent(array('allowedFilters' => null));
	}

/**
 * testSetSettingsDebuggingExceptionsUnknownString
 *
 * @return void
 */
	public function testSetSettingsDebuggingExceptionsUnknownString() {
		// test setting an unknown string value
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponents allowedFilters configuration is a unknown string value: lala');
		$this->setUpComponent(array('allowedFilters' => 'lala'));
	}

/**
 * testSetSettingsDebuggingExceptionsUnknownField
 *
 * @return void
 */
	public function testSetSettingsDebuggingExceptionsUnknownField() {
		// test setting an array of fields, but imaginary_field doesn't exist
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponents allowedFilters configuration allows filtering on Article.imaginary_field, but this is field doesn\'t exist in the models schema.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'Article.imaginary_field', 'Article.published')));
	}

/**
 * testSetSettingsDebuggingExceptionsUnknownModel
 *
 * @return void
 */
	public function testSetSettingsDebuggingExceptionsUnknownModel() {
		// test setting an array of fields, but ImaginaryModel doesn't exist
		$this->setExpectedException('BanchaException', 'The TestBanchaPaginatorComponentsController is missing the model ImaginaryModel, but has a configuration for this model in BanchaPaginatorComponents allowedFilters configuration. Please make sure to define the controllers uses property or use the beforeFilter for loading.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'ImaginaryModel.title', 'Article.published')));
	}

/**
 * testSanitizeConditionsDebugModeExistingProhibitedFieldNone
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeExistingProhibitedFieldNone() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.title' => 'Titel 01'));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.title, which is not allowed according to the TestBanchaPaginatorComponents BanchaPaginatorComponents allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testSanitizeConditionsDebugModeImaginarlyProhibitedFieldNone
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeImaginarlyProhibitedFieldNone() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.imaginary_field' => 'Titel 01'));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.imaginary_field, which is not allowed according to the TestBanchaPaginatorComponents BanchaPaginatorComponents allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testSanitizeConditionsDebugModeExistingProhibitedFieldArray
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeProhibitedFieldArray() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(
			array(
				'allowedFilters' => array('Article.title', 'Article.body', 'Article.date')
			),
			array(
				'Article.title' => 'Titel 01',
				'Article.published' => true)
		);

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.published, which is not allowed according to the TestBanchaPaginatorComponents BanchaPaginatorComponents allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testSanitizeConditionsDebugModeAllowedFieldArray
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeAllowedFieldArray() {
		// test using allowed filters
		$this->setUpComponent(
			array(
				'allowedFilters' => array('Article.title', 'Article.body', 'Article.published')
			),
			array(
				'Article.title' => 'Titel 01',
				'Article.published' => true
			)
		);

		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.title']));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.published']));
		$this->assertEquals('Titel 01', $this->Controller->request['named']['conditions']['Article.title']);
		$this->assertEquals(true, $this->Controller->request['named']['conditions']['Article.published']);

		// these filters should be allowed, no no exceptions here
		$this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testSanitizeConditionsDebugModeProhibitedFieldAssociations
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeProhibitedFieldAssociations() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(
			array(
				'allowedFilters' => 'associations'
			),
			array(
				'Article.title' => 'Titel 01'
			)
		);

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.title, which is not allowed according to the TestBanchaPaginatorComponents BanchaPaginatorComponents allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testSanitizeConditionsDebugModeAllowedFieldAssociations
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeAllowedFieldAssociations() {
		// test using allowed filters
		$this->setUpComponent(
			array(
				'allowedFilters' => 'associations'
			),
			array(
				'Article.user_id' =>2
			)
		);
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.user_id']));
		$this->assertEquals(2, $this->Controller->request['named']['conditions']['Article.user_id']);

		// these filters should be allowed, no no exceptions here
		$this->BanchaPaginatorComponent->paginate('Article');

		// should also allow filtering via id
		$this->setUpComponent(
			array(
				'allowedFilters' => 'associations'
			),
			array(
				'Article.id' => 1001
			)
		);
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.id']));
		$this->assertEquals(1001, $this->Controller->request['named']['conditions']['Article.id']);

		// these filters should be allowed, no no exceptions here
		$this->BanchaPaginatorComponent->paginate('Article');
	}
/**
 * testSanitizeConditionsDebugModeAllowedFieldAll
 *
 * @return void
 */
	public function testSanitizeConditionsDebugModeAllowedFieldAll() {
		// test using allowed filters
		$this->setUpComponent(
			array(
				'allowedFilters' => 'all'
			),
			array(
				'Article.title' => 'Titel 01',
				'Article.published' => true
			)
		);
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.title']));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.published']));
		$this->assertEquals('Titel 01', $this->Controller->request['named']['conditions']['Article.title']);
		$this->assertEquals(true, $this->Controller->request['named']['conditions']['Article.published']);

		// these filters should be allowed, no no exceptions here
		$this->BanchaPaginatorComponent->paginate('Article');
	}
/**
 * testBanchaSettings
 *
 * In Bancha requests the $banchaSetting should override the default $settings
 *
 * @return void
 */
	public function testBanchaSettings() {
		// set up
		$this->setUpComponent(array());
		$this->Controller->Article->recursive = -1;

		// this would be for normal requests
		$this->BanchaPaginatorComponent->settings = array(
			'maxLimit' => 10
		);
		// we want to see using this from Bancha
		$this->BanchaPaginatorComponent->banchaSettings = array(
			'maxLimit' => 100
		);

		// test with a limit of 20
		$this->Controller->request->params['named']['limit'] = 20;
		$result = $this->BanchaPaginatorComponent->paginate('Article');
		$this->assertEquals(20, count($result));

		// test with a limit of 100 (fits bancha settings maxLimit)
		$this->Controller->request->params['named']['limit'] = 100;
		$result = $this->BanchaPaginatorComponent->paginate('Article');
		$this->assertEquals(100, count($result));

		// test with a limit of 150 (should throw an exception in debug level)
		Configure::write('debug', 2);
		$this->Controller->request->params['named']['limit'] = 150;
		$this->setExpectedException('BanchaException', 'The pageSize(150) you set is bigger then the maxLimit(100) set in CakePHP.');
		$result = $this->BanchaPaginatorComponent->paginate('Article');
	}

/**
 * testPaginationConditionApplying
 *
 * Test case for GitHub Issue #79
 *
 * @return void
 */
	public function testPaginationConditionApplying() {
		$result = $this->testAction('/testbanchapaginatorcomponents/getpaginationconditionsargument');
		$this->assertEquals(1, count($result));
		$this->assertEquals('1001', $result[0]['Article']['id']);

		$result = $this->testAction('/testbanchapaginatorcomponents/getPaginationConditionsProperty');
		$this->assertEquals(1, count($result));
		$this->assertEquals('1001', $result[0]['Article']['id']);
	}
}


