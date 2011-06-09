<?php

/**
 * BanchaDispatcherTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Routing/BanchaDispatcher.php';
require_once 'Routing/BanchaSingleDispatcher.php';
require_once 'Network/BanchaResponse.php';

/**
 * @package bancha.libs
 */
class BanchaDispatcherTest extends CakeTestCase {
	
	public function testDispatch() {
		$banchaRequest = $this->getMock('BanchaRequest', array('getRequests'));
		$banchaRequest->expects($this->any())
					  ->method('getRequests')
					  ->will($this->returnValue(array(
						new CakeRequest('/my/testaction1'),
						new CakeRequest('/my/testaction2')
					  )));
		
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch($banchaRequest)->send());
		
		$this->assertEquals('Hello World', $responses[0]['body']['text']);
		$this->assertEquals('foobar', $responses[1]['body']['text']);
	}
	
}

/**
 * MyController class
 *
 * @package       bancha.tests.cases
 */
class MyController extends AppController {
	
	public function testaction1() {
		return array('text' => 'Hello World!');
	}
	
	public function testaction2() {
		return array('text' => 'foobar');
	}
	
}

