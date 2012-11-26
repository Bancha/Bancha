<?php
/**
 * BanchaControllerTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * BanchaControllerTest
 * @package       Bancha
 * @category      tests
 */
class BanchaControllerTest extends ControllerTestCase {

	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	public function testBanchaApiConfiguration() {
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check primary Bancha configurations
		$this->assertTrue(isset($api->metadata->_UID));

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));
	}

	public function testBanchaApiWithOneModelMetadata() {
		$response = $this->testAction('/bancha-api/models/User.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that correct metadata is send
		$this->assertFalse(isset($api->metadata->Article));
		$this->assertFalse(isset($api->metadata->ArticlesTag));
		$this->assertFalse(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User)); // <-- this should be available
		$this->assertFalse(isset($api->metadata->HelloWorld));
		$this->assertFalse(isset($api->metadata->Bancha));

		// test meta data structure
		$this->assertEquals('id', $api->metadata->User->idProperty);
		$this->assertTrue(is_array($api->metadata->User->fields));
		$this->assertTrue(is_array($api->metadata->User->validations));
		$this->assertTrue(is_array($api->metadata->User->associations));
		$this->assertTrue(is_array($api->metadata->User->sorters));

	}

	public function testBanchaApiWithAllMetadata() {
		$response = $this->testAction('/bancha-api/models/all.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));

		// check Ext.Direct configurations
		$this->assertEquals('/bancha-dispatcher.php', substr($api->url,-22,22)); //strip the absolute path, otherwise it doesn't probably work in the terminal
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		// check exposed methods
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));

		// check that correct metadata is send
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->HelloWorld)); // there is no exposed model, so no meta data
		$this->assertFalse(isset($api->metadata->Bancha)); // there is no exposed model, so no meta data
	}
	
	
}

    