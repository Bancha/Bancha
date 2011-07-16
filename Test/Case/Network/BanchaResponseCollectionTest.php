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
		$response3 = array(
			'body'	=> array('message' => 'This is an exception'),
		);
		$request1 = new CakeRequest();
		$request1->addParams(array('controller' => 'foo', 'action' => 'bar'));
		$request2 = new CakeRequest();
		$request2->addParams(array('controller' => 'bar', 'action' => 'foo'));
		$request3 = new CakeRequest();
		$request3->addParams(array('controller' => 'foo', 'action' => 'error'));
		
		$expectedResponse1 = array(
			'type'		=> 'rpc',
			'tid'		=> 1,
			'action'	=> 'foo',
			'method'	=> 'bar',
			'result'	=> array('message' => 'Hello World'),
		);
		$expectedResponse2 = array(
			'type'		=> 'rpc',
			'tid'		=> 2,
			'action'	=> 'bar',
			'method'	=> 'foo',
			'result'	=> array('message' => 'Hello Bancha'),
		);
		$expectedResponse3 = array(
			'type'		=> 'exception',
			'tid'		=> 3,
			'action'	=> 'foo',
			'method'	=> 'error',
			'result'	=> 'This is an exception',
		);
		
		$collection = new BanchaResponseCollection();
		$collection->addResponse(1, new CakeResponse($response1), $request1)
				   ->addResponse(2, new CakeResponse($response2), $request2)
				   ->addException(3, new Exception($response3['body']['message']), $request3);
		
		$actualResponse = $collection->getResponses()->body();
		
		$this->assertEquals(
			json_decode(json_encode(array($expectedResponse1, $expectedResponse2, $expectedResponse3))),
			json_decode($actualResponse)
		);
		
		$actualResponse = json_decode($actualResponse);
		$this->assertEquals(json_decode(json_encode($expectedResponse1['result'])), $actualResponse[0]->result);
		$this->assertEquals(json_decode(json_encode($expectedResponse2['result'])), $actualResponse[1]->result);
		$this->assertEquals(json_decode(json_encode($expectedResponse3['result'])), $actualResponse[2]->result);
	}
}
