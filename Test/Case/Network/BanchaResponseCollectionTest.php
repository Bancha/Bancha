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
			'success'	=> true,
			'message'	=> 200,
			'body'		=> 'test1',
		);
		$response2 = array(
			'success'	=> true,
			'message'	=> 200,
			'body'		=> 'test1',
		);
		
		$expectedResponse1 = array(
			'success'	=> true,
			'message'	=> 200,
			'data'		=> 'test1',
		);
		$expectedResponse2 = array(
			'success'	=> true,
			'message'	=> 200,
			'data'		=> 'test1',
		);
		
		$collection = new BanchaResponseCollection();
		$collection->addResponse(new CakeResponse($response1))
				   ->addResponse(new CakeResponse($response2));
		
		$actualResponse = $collection->getResponses()->body();
		$this->assertEquals(json_encode(array($expectedResponse1, $expectedResponse2)), $actualResponse);
		
		$actualResponse = json_decode($actualResponse);
		$this->assertEquals($expectedResponse1['data'], $actualResponse[0]->data);
		$this->assertEquals($expectedResponse2['data'], $actualResponse[1]->data);
	}
}
