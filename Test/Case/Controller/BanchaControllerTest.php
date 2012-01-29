<?php
/**
 * BanchaControllerTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 */

/**
 * BanchaControllerTest
 * @package       Bancha
 * @category      tests
 */
class BanchaControllerTest extends ControllerTestCase {

	public $fixtures = array('plugin.bancha.article','plugin.bancha.user','plugin.bancha.tag','plugin.bancha.articles_tag');

	public function testIndexNoMetadata()
	{
		$response = $this->testAction('/bancha-api.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		$this->assertEquals('/bancha.php', $api->url);
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);
		$this->assertTrue(isset($api->metadata->_UID));
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));
	}

	public function testIndexOneMetadata()
	{
		$response = $this->testAction('/bancha-api/models/User.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		$this->assertEquals('/bancha.php', $api->url);
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);
		$this->assertTrue(isset($api->metadata->_UID));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertFalse(isset($api->metadata->Article));
		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));
	}

	public function testIndexAllMetadata()
	{
		$response = $this->testAction('/bancha-api/models/all.js');
		$api = json_decode(substr($response, strpos($response, '=')+1));
		$this->assertEquals('/bancha.php', $api->url);
		$this->assertEquals('Bancha.RemoteStubs', $api->namespace);
		$this->assertEquals('remoting', $api->type);

		$this->assertTrue(isset($api->metadata->_UID));
		$this->assertTrue(isset($api->metadata->User));
		$this->assertTrue(isset($api->metadata->Article));
		$this->assertTrue(isset($api->metadata->ArticlesTag));
		$this->assertTrue(isset($api->metadata->Tag));
		$this->assertFalse(isset($api->metadata->HelloWorld));
		$this->assertFalse(isset($api->metadata->Bancha));

		$this->assertTrue(isset($api->actions->Article));
		$this->assertTrue(isset($api->actions->ArticlesTag));
		$this->assertTrue(isset($api->actions->Tag));
		$this->assertTrue(isset($api->actions->User));
		$this->assertTrue(isset($api->actions->HelloWorld));
		$this->assertTrue(isset($api->actions->Bancha));
	}


	// TODO add tests here, see $this->testAction('/posts/add
	
	
}

    