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

/**
 * This tests ensures that two requests, which are sent in the same batch request are executed in order of their
 * transaction IDs. Therefore the order is wrong in the input data and the test ensures that their order is correct
 * in the output data.
 *
 */
	public function testEditEditOneRequest() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		$dispatcher = new BanchaDispatcher();

		$client_id = uniqid();

		$rawPostData = json_encode(array(
			array(
				'action'		=> 'Articles',
				'method'		=> 'update',
				'tid'			=> 2,
				'type'			=> 'rpc',
				'data'			=> array(
					'__bcid'		=> $client_id,
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
					'__bcid'		=> $client_id,
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

/**
 * This test ensures that multiple requests, which are sent with multiple requests are executed in the correct order.
 * Thus it ensures that a request with a higher transaction ID is not executed before a request with a lower TID.
 *
 */
	public function testEditEditMultipleRequests() {
		// Preparation: create article
		$article = new Article();
		$article->create();
		$article->save(array('title' => 'foo'));

		// Execute two requests in parallel.
		$clientId = uniqid();
		// The syntax of the fake_request script is
		// php _fake_request.php client_id article_id tid new_title sleep_time
		exec('php ' . dirname(__FILE__) . '/_fake_request.php ' . $clientId . ' ' . $article->id . ' 1 foobar 5 '
			. '>/dev/null &');
		sleep(3);
		exec('php ' . dirname(__FILE__) . '/_fake_request.php ' . $clientId . ' ' . $article->id . ' 2 barfoo 0 '
			. ' >/dev/null &');

		sleep(8);

		$data = $article->read(null, $article->id);
		$this->assertEquals('foobar', $data['Article']['title']);

		// Clean up operations: delete article
		$article->delete();
	}

}
