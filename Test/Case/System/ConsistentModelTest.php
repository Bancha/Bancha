<?php
/**
 * ConsistentModelTest file.
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
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('AppModel', 'Model');
App::uses('Article', 'Model');

require_once dirname(__FILE__) . '/../../../Model/Behavior/BanchaBehavior.php';

// TODO: refactor to use real test models.
require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * ConsistentModelTest
 *
 * @package bancha.libs
 */
class ConsistentModelTest extends CakeTestCase {
	
	public function setUp() {
		parent::setUp();
	}
	
	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}
	
	public function testEditEditOneRequest()
	{
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));
			
		$dispatcher = new BanchaDispatcher();
		
		$rawPostData = json_encode(array(
			array(
				'action'		=> 'Articles',
				'method'		=> 'update',
				'tid'			=> 2,
				'type'			=> 'rpc',
				'data'			=> array(
					'id'			=> $article->id,
					'title'			=> 'foobar',
					'published'		=> true,
				),
			),
			array(
				'action'		=> 'Articles',
				'method'		=> 'update',
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(
					'id'			=> $article->id,
					'title'			=> 'barfoo',
					'published'		=> true,
					),
			),
		));
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		
		$data = $article->read(null, $article->id);
		$this->assertEquals('foobar', $data['Article']['title']);
		
		// Clean up operations: delete article
		$article->delete();
	}
	
	// public function testEditEditMultipleRequests()
	// {
	// 	// Preparation: create article
	// 	$article = new Article();
	// 	$article->create();
	// 	$article->save(array('title' => 'foo'));
	// 		
	// 	$dispatcher = new BanchaDispatcher();
	// 	
	// 	$rawPostData1 = json_encode(array(
	// 		'action'		=> 'Articles',
	// 		'method'		=> 'update',
	// 		'tid'			=> 2,
	// 		'type'			=> 'rpc',
	// 		'data'			=> array(
	// 			'id'			=> $article->id,
	// 			'title'			=> 'foobar',
	// 			'published'		=> true,
	// 		),
	// 	));
	// 	$responses1 = json_decode($dispatcher->dispatch(
	// 		new BanchaRequestCollection($rawPostData1), array('return' => true)
	// 	));
	// 	
	// 	$rawPostData2 = json_encode(array(
	// 		'action'		=> 'Articles',
	// 		'method'		=> 'update',
	// 		'tid'			=> 1,
	// 		'type'			=> 'rpc',
	// 		'data'			=> array(
	// 			'id'			=> $article->id,
	// 			'title'			=> 'barfoo',
	// 			'published'		=> true,
	// 		),
	// 	));
	// 	$responses2 = json_decode($dispatcher->dispatch(
	// 		new BanchaRequestCollection($rawPostData2), array('return' => true)
	// 	));
	// 	
	// 	$data = $article->read(null, $article->id);
	// 	$this->assertEquals('foobar', $data['Article']['title']);
	// }
	
}