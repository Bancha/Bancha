<?php
/**
 * BanchaComponentTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha.Test.Case.Controller.Component
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('BanchaComponent', 'Bancha.Controller/Component');

/**
 * TestBanchaComponentController
 *
 * A fake controller to test against
 *
 * @package       Bancha
 * @category      tests
 *
 */	
class TestBanchaComponentController extends Controller {
	public $uses = array('Article');
}

/**
 * BanchaComponentTest
 * @package       Bancha.Test.Case.Controller.Component
 * @category      tests
 */
class BanchaComponentTest extends CakeTestCase {
    public $fixtures = array('plugin.bancha.article');

/**
 * This method creates a controller and a component with the given settings
 */
	public function setUpComponent($settings, $conditions = array()) {
        // Setup our component and fake test controller
        // See http://book.cakephp.org/2.0/en/development/testing.html#testing-components
        $Collection = new ComponentCollection();
		$this->BanchaComponent = new BanchaComponent($Collection, $settings);
        $CakeRequest = new CakeRequest();
        $CakeRequest->params['isBancha'] = true; // fake a Bancha request
        $CakeRequest->params['named']['conditions'] = $conditions; // this exist in every Bancha request
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestBanchaComponentController($CakeRequest, $CakeResponse);
        $this->BanchaComponent->startup($this->Controller);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
        // Clean up after we're done
		unset($this->BanchaComponent);
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
		$this->assertEquals('all', $this->BanchaComponent->allowedFilters);

		// test setting allowed filters to associations
		$this->setUpComponent(array('allowedFilters' => 'associations'));
		$this->assertEquals('associations', $this->BanchaComponent->allowedFilters);

		// test setting allowed filters to none
		$this->setUpComponent(array('allowedFilters' => 'none'));
		$this->assertTrue(is_array($this->BanchaComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaComponent->allowedFilters), 0);

		$this->setUpComponent(array('allowedFilters' => array()));
		$this->assertTrue(is_array($this->BanchaComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaComponent->allowedFilters), 0);

		// test setting an array of existing values
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'Article.body', 'Article.published')));
		$this->assertTrue(is_array($this->BanchaComponent->allowedFilters));
		$this->assertEquals(count($this->BanchaComponent->allowedFilters), 3);
	}

/**
 * testSetSettings_DebuggingExceptions_Null
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_Null() {
		// test setting an to null
		$this->setExpectedException('BanchaException', 'The BanchaComponent::allowedFilters configuration needs to be set.');
		$this->setUpComponent(array('allowedFilters' => null));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownString
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownString() {
		// test setting an unknown string value
		$this->setExpectedException('BanchaException', 'The BanchaComponent::allowedFilters configuration is a unknown string value: lala');
		$this->setUpComponent(array('allowedFilters' => 'lala'));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownField
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownField() {
		// test setting an array of fields, but imaginary_field doesn't exist
		$this->setExpectedException('BanchaException', 'The BanchaComponent::allowedFilters configuration allows filtering on Article.imaginary_field, but this is field doesn\'t exist in the models schema.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'Article.imaginary_field', 'Article.published')));
	}

/**
 * testSetSettings_DebuggingExceptions_UnknownModel
 *
 * @return void
 */
	public function testSetSettings_DebuggingExceptions_UnknownModel() {
		// test setting an array of fields, but ImaginaryModel doesn't exist
		$this->setExpectedException('BanchaException', 'The TestBanchaComponentController is missing the model ImaginaryModel, but has a configuration for this model in BanchaComponent::allowedFilters. Please make sure to define the controllers uses property or use the beforeFilter for loading.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title', 'ImaginaryModel.title', 'Article.published')));
	}

/**
 * testSanitizeConditions_DebugMode_ExistingProhibitedField_None
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ExistingProhibitedField_None() {
		// test using prohibit filters
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter the by Article.title, which is not allowed according to the TestBanchaComponent BanchaComponent::allowedFilters configuration.');
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.title'=>'Titel 01'));
	}

/**
 * testSanitizeConditions_DebugMode_ImaginarlyProhibitedField_None
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ImaginarlyProhibitedField_None() {
		// test using prohibit filters
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter the by Article.imaginary_field, which is not allowed according to the TestBanchaComponent BanchaComponent::allowedFilters configuration.');
		$this->setUpComponent(array('allowedFilters' => 'none'), array('Article.imaginary_field'=>'Titel 01'));
	}

/**
 * testSanitizeConditions_DebugMode_ExistingProhibitedField_Array
 *
 * @return void
 */
	public function testSanitizeConditions_DebugMode_ProhibitedField_Array() {
		// test using prohibit filters
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter the by Article.published, which is not allowed according to the TestBanchaComponent BanchaComponent::allowedFilters configuration.');
		$this->setUpComponent(array('allowedFilters' => array('Article.title','Article.body','Article.date')), array('Article.title'=>'Titel 01','Article.published'=>true));
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
		$this->setExpectedException('BanchaException', 'The last ExtJS/Sencha Touch request tried to filter the by Article.title, which is not allowed according to the TestBanchaComponent BanchaComponent::allowedFilters configuration.');
		$this->setUpComponent(array('allowedFilters' => 'associations'), array('Article.title'=>'Titel 01'));
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
}

    
