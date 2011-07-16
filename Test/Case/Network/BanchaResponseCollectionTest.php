<?php
/**
 * BanchaCrudTest file.
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
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */
class BanchaResponseCollectionTest extends CakeTestCase
{

	function testGetResponses() {
		$response1 = array(
			'body'	=> array('message' => 'Hello World'),
		);
		$response2 = array(
			'body'	=> array('message' => 'Hello Bancha'),
		);
		$response3 = new Exception('This is an exception'); $exception_line = __LINE__;
		$request1 = new CakeRequest();
		$request1->addParams(array('controller' => 'foo', 'action' => 'bar'));
		$request2 = new CakeRequest();
		$request2->addParams(array('controller' => 'bar', 'action' => 'foo'));
		$request3 = new CakeRequest();
		$request3->addParams(array('controller' => 'foo', 'action' => 'error'));
		
		$collection = new BanchaResponseCollection();
		$collection->addResponse(1, new CakeResponse($response1), $request1)
				   ->addResponse(2, new CakeResponse($response2), $request2)
				   ->addException(3, $response3, $request3);
		
		$actualResponse = json_decode($collection->getResponses()->body());
		
		$this->assertEquals('rpc', $actualResponse[0]->type);
		$this->assertEquals(1, $actualResponse[0]->tid);
		$this->assertEquals('foo', $actualResponse[0]->action);
		$this->assertEquals('bar', $actualResponse[0]->method);
		$this->assertEquals((object)array('message' => 'Hello World'), $actualResponse[0]->result);
		
		$this->assertEquals('rpc', $actualResponse[1]->type);
		$this->assertEquals(2, $actualResponse[1]->tid);
		$this->assertEquals('bar', $actualResponse[1]->action);
		$this->assertEquals('foo', $actualResponse[1]->method);
		$this->assertEquals((object)array('message' => 'Hello Bancha'), $actualResponse[1]->result);
		
		$this->assertEquals('exception', $actualResponse[2]->type);
		$this->assertEquals('This is an exception', $actualResponse[2]->message);
		$this->assertEquals('In file "' . __FILE__ . '" on line ' . $exception_line . '.', $actualResponse[2]->where);
	}
}
