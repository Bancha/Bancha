<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       bancha.libs
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

/**
 * BanchaRequestCollectionTest
 *
 * @package bancha.libs
 */
class BanchaRequestCollectionTest extends CakeTestCase
{
	
	function testgetRequests() {
		$rawPostData = array(
			'action'	=> 'Test',
			'method'	=> 'create',
			'data'		=> null,
			'type'		=> 'rpc',
			'tid'		=> 1,
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals(1, count($requests));
		$this->assertEquals($requests[0]['action'], 'add');
	}

	function testgetRequestsMultiple() {
		$rawPostData = array(
			array(
				'action'	=> 'Test',
				'method'	=> 'create',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 1,
			),
			array(
				'action'	=> 'Test',
				'method'	=> 'update',
				'data'		=> null,
				'type'		=> 'rpc',
				'tid'		=> 2,
			),
		);

		$collection = new BanchaRequestCollection(json_encode($rawPostData));
		$requests = $collection->getRequests();
		
		$this->assertEquals(2, count($requests));
		$this->assertEquals($requests[0]['action'], 'add');
		$this->assertEquals($requests[1]['action'], 'edit');
	}
}
