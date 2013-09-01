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
 * TestBanchaPaginatorComponentController
 *
 * A fake controller to test against
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.1.0
 */
class TestBanchaPaginatorComponentController extends Controller {
	public $uses = array('Article');
}

/**
 * BanchaPaginatorComponentTest
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.1.0
 */
class BanchaPaginatorComponentTest extends CakeTestCase {
    public $fixtures = array('plugin.bancha.article');

/**
 * This method creates a controller and a component with the given settings
 */
	public function setUpComponent($settings, $conditions = array()) {
        // Setup our component and fake test controller
        // See http://book.cakephp.org/2.0/en/development/testing.html#testing-components

        // setup the controller
        $CakeRequest = new CakeRequest();
        $CakeRequest->params['isBancha'] = true; // fake a Bancha request
        $CakeRequest->params['named']['conditions'] = $conditions; // this exist in every Bancha request
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestBanchaPaginatorComponentController($CakeRequest, $CakeResponse);

        // setup the component collection
        $Collection = new ComponentCollection();
        $Collection->init($this->Controller);

        // setup the paginator component
		$this->BanchaPaginatorComponent = new BanchaPaginatorComponent($Collection, $settings);
        $this->BanchaPaginatorComponent->startup($this->Controller);
	}

	public function setUp() {
		parent::setUp();

		// keep debug level
		$this->originalDebugLevel = Configure::read('debug');
	}

	public function tearDown() {
		parent::tearDown();

		// reset the debug level
		Configure::write('debug', $this->originalDebugLevel);

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
 * testSetSettings_DebuggingExceptions_Null
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_Null() {
		// test setting an to null
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponent::allowedFilters configuration needs to be set.');
		$this->setUpComponent(array('allowedFilters' => null));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownString
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownString() {
		// test setting an unknown string value
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponent::allowedFilters configuration is a unknown string value: lala');
		$this->setUpComponent(array('allowedFilters' => 'lala'));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownField
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownField() {
		// test setting an array of fields, but imaginary_field doesn't exist
		$this->setExpectedException('BanchaException', 'The BanchaPaginatorComponent::allowedFilters configuration allows filtering on Article.imaginary_field, but this is field doesn\'t exist in the models schema.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'Article.imaginary_field', 'Article.published')));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownModel
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownModel() {
		// test setting an array of fields, but ImaginaryModel doesn't exist
		$this->setExpectedException('BanchaException', 'The TestBanchaPaginatorComponentController is missing the model ImaginaryModel, but has a configuration for this model in BanchaPaginatorComponent::allowedFilters. Please make sure to define the controllers uses property or use the beforeFilter for loading.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'ImaginaryModel.title', 'Article.published')));
	}

/**
 * testSanitizeConditions_DebugMode_ExistingProhibitedField_None
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ExistingProhibitedField_None() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.title'=>'Titel 01'));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.title, which is not allowed according to the TestBanchaPaginatorComponent BanchaPaginatorComponent::allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate();
	}

/**
 * testSanitizeConditions_DebugMode_ImaginarlyProhibitedField_None
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ImaginarlyProhibitedField_None() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.imaginary_field'=>'Titel 01'));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.imaginary_field, which is not allowed according to the TestBanchaPaginatorComponent BanchaPaginatorComponent::allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate();
	}

/**
 * testSanitizeConditions_DebugMode_ExistingProhibitedField_Array
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ProhibitedField_Array() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => array('Article.title','Article.body','Article.date')), array('Article.title'=>'Titel 01','Article.published'=>true));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.published, which is not allowed according to the TestBanchaPaginatorComponent BanchaPaginatorComponent::allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate();
	}

/**
 * testSanitizeConditions_DebugMode_AllowedField_Array
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_AllowedField_Array() {
		// test using allowed filters
		$this->setUpComponent(array('allowedFilters' => array('Article.title','Article.body','Article.published')), array('Article.title'=>'Titel 01','Article.published'=>true));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.title']));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.published']));
		$this->assertEquals('Titel 01', $this->Controller->request['named']['conditions']['Article.title']);
		$this->assertEquals(true, $this->Controller->request['named']['conditions']['Article.published']);
	}

/**
 * testSanitizeConditions_DebugMode_ProhibitedField_Associations
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ProhibitedField_Associations() {
		// test using prohibit filters
		// it should not yet throw an error when setting up
		$this->setUpComponent(array('allowedFilters' => 'associations'), array('Article.title'=>'Titel 01'));

		// if should trown an error when paginating
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter by Article.title, which is not allowed according to the TestBanchaPaginatorComponent BanchaPaginatorComponent::allowedFilters configuration.');
		$this->BanchaPaginatorComponent->paginate();
	}

/**
 * testSanitizeConditions_DebugMode_AllowedField_Associations
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_AllowedField_Associations() {
		// test using allowed filters
		$this->setUpComponent(array('allowedFilters' => 'associations'), array('Article.user_id'=>2));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.user_id']));
		$this->assertEquals(2, $this->Controller->request['named']['conditions']['Article.user_id']);
	}
/**
 * testSanitizeConditions_DebugMode_AllowedField_All
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_AllowedField_All() {
		// test using allowed filters
		$this->setUpComponent(array('allowedFilters' => 'all'), array('Article.title'=>'Titel 01','Article.published'=>true));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.title']));
		$this->assertTrue(isset($this->Controller->request['named']['conditions']['Article.published']));
		$this->assertEquals('Titel 01', $this->Controller->request['named']['conditions']['Article.title']);
		$this->assertEquals(true, $this->Controller->request['named']['conditions']['Article.published']);
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
		$this->setExpectedException('BanchaException', 'The pageSize you set is bigger then the maxLimit set in CakePHP.');
		$result = $this->BanchaPaginatorComponent->paginate('Article');
	}
}


