<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaRequestCollection', 'Bancha');
App::import('Lib','Bancha.Bancha.Network');

echo realpath(dirname(__FILE__) . '/../../../lib/Bancha') . "\n\n";


//TODO: Unnötig ??
set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Network/BanchaRequestCollection.php';
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
