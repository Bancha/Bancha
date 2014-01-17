<?php
/**
 * ConsistentModelTest file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package		Bancha.Test.Case.System
 * @copyright	Copyright 2011-2013 codeQ e.U.
 * @link		http://banchaproject.org Bancha Project
 * @since		Bancha v 2.3.0
 * @author		Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author		Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('AppModel', 'Model');
App::uses('Article', 'Model');

// TODO: refactor to use real test models.
require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * ConsistentModelTest
 *
 * One of the challenges when developing the consistent model functionality for 
 * Bancha is to test the written code. PHP offers no possibility to run code in 
 * parallel and therefore it is not easily possible to simulate the scenario 
 * where a request takes a very long time to process, which results in resending 
 * this request. Our solution for this problem is to move the code to handle the 
 * request in a separate test script and use exec() to execute two PHP scripts 
 * simultaneously in the background.
 * 
 * @package	    Bancha.Test.Case.System
 * @author		Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author		Roland Schuetz <mail@rolandschuetz.at>
 * @since		Bancha v 2.3.0
 */
class ConsistentModelTest extends CakeTestCase {

	public $fixtures = array(
		'plugin.bancha.article',
		'plugin.bancha.articles_tag'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
	}

/**
 * Just check if we can save client_ids in this environment and
 * if we can run background tasks.
 *
 * @return void
 */
	public function testSetUp() {
		// check if file permissions are fine
		$clientFolder = TMP . 'bancha-clients';
		$clientFile = $clientFolder . DS . 'ConsistentModelTest' . '.txt';

		if (!is_dir($clientFolder)) {
			$this->assertTrue(
				@mkdir($clientFolder),
				"\n\n\nCould not create folder " . $clientFolder . ". Please check permissions!\n\n\n"
			);
		}
		$this->assertTrue(
			false !== @file_put_contents($clientFile, 'lala'),
			"\n\n\nCould not create file " . $clientFile . ". Please check permissions!\n\n\n"
		);
		$this->assertTrue(unlink($clientFile));

		// check if we can run a background task
	  	$disabled = explode(', ', ini_get('disable_functions'));
		$fn = (substr(php_uname(), 0, 7) == 'Windows') ? 'popen' : 'exec';
		$this->assertFalse(
			in_array($fn, $disabled), 
			'Test Suite is not able to run tasks in the background using '.$fn.
			', therefore this these tasks can\'t be run.'
		);
	}

/**
 * Helper function for skipping.
 *
 * @return void
 */
	public function skipIfSqlite() {
		$config = ConnectionManager::enumConnectionObjects();
		$this->skipIf(
			$config['default']['datasource'] == 'Database/Sqlite',
			'Default database needs to be persistent for this test'
		);
	}

/**
 * This will execute $cmd in the background (no cmd window) without PHP 
 * waiting for it to finish, on both Windows and Unix. 
 *
 * @param string         $cmd        The command to execute
 * @param boolean|String $resultFile A string to save the result into a file
 * @return void
 */
	protected function _execInBackground($cmd, $resultFile = false) {
		if (substr(php_uname(), 0, 7) == 'Windows') {
			pclose(popen('start /B ' . $cmd, 'r')); // not sure how to get the response on windows.
		} else {
			$resultFile = $resultFile ? dirname(__FILE__) . DS . $resultFile : '/dev/null';
			exec($cmd . ' > ' . $resultFile . ' &');   
		}
	}

/**
 * Fakes a Bancha request in a new environment.
 *
 * If the sleep time not zero the command if spin up in the background 
 * in a differnt process.
 * 
 * @param  string  $clientId   The clients uuid
 * @param  string  $tid        The tid
 * @param  string  $articleId  The article id to edit
 * @param  string  $newTitle  The new article title
 * @param  integer $sleepTime The time to wait before the execute is fiished, fake processing time
 * @param  integer $resultFile The filename the result should be written to, if this is a background task
 * @return string              The responses array, if $sleepTime==0
 */
	protected function _fakeRequest($clientId, $tid, $articleId, $newTitle, $sleepTime=0, $saveResult=false) {
		// The syntax of the fake_request script is
		// php _fake_request.php client_id article_id tid newTitle sleepTime
		// These processes are executed in the background and we do not need the output.

		$cmd = 'php ' . dirname(__FILE__) . '/_fake_request.php ' . $clientId . ' ' .
				$articleId . ' ' . $tid . ' ' . $newTitle . ' ' . $sleepTime;

		if ($sleepTime == 0) {
			$result = array();
			exec($cmd, $result);
			return json_decode(implode('', $result));
		}

		// run it in the background
		$this->_execInBackground($cmd, $saveResult ? $clientId . '-' . $tid . '.txt' : false);
	}

/**
 * Retrieves the result from an async face request
 * 
 * @param  string  $clientId   The clients uuid
 * @param  string  $tid        The tid
 * @return array               The response as decoded array
 */
	private function getAsyncResponse($clientId, $tid) {
		$filename = dirname(__FILE__) . DS . $clientId . '-' . $tid . '.txt';
		$result = json_decode(file_get_contents($filename));
		unlink($filename);
		return $result;
	}

/**
 * This test ensures that requests in normal order are executed correctly,
 * and that duplicate requests get discarded.
 * See http://bancha.io/documentation-pro-models-consistent-transactions.html#duplicated-requests
 *
 * @return void
 */
	public function testInSequence() {
		$this->skipIf(Configure::read('fastTestsOnly'), 'Skipped slow test case.');
		$this->skipIfSqlite();
		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...),

		$clientId = uniqid();

		// execute one edit, and then a second one in sequence
		$this->_fakeRequest($clientId, 1, 1001, 'foobar');
		$this->_fakeRequest($clientId, 2, 1001, 'barfoo');

		// Read article from database and check if the value is correct.
		$article = ClassRegistry::init('Article');
		$article->recursive = -1;
		$data = $article->read(null, 1001);
		$this->assertEquals('barfoo', $data['Article']['title']);

		// in the meanwhile change the record
		$article->saveField('title', 'internally changed');
		// then imitate a duplicated request, which should get discarded
		$this->_fakeRequest($clientId, 2, 1001, 'barfoo');

		// check that it got discarded
		$data = $article->read(null, 1001);
		$this->assertEquals('internally changed', $data['Article']['title']);

		// check that a differnt client id get's his own counter
		$this->_fakeRequest(uniqid(), 1, 1001, 'lastChange');

		// check that it got changed
		$data = $article->read(null, 1001);
		$this->assertEquals('lastChange', $data['Article']['title']);
	}

/**
 * This test ensures that batched requests are handled correctly.
 * 
 * @return void
 */
	public function testBatchedRequests() {
		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...),

		$clientId = uniqid();
		$rawPostData = json_encode(array(
			array(
				'action'		=> 'Articles',
				'method'		=> 'update',
				'tid'			=> 1,
				'type'			=> 'rpc',
				'data'			=> array(array('data' => array(
					'__bcid'		=> $clientId,
					'id'			=> 1001,
					'title'			=> 'foobar'
				))),
			),
			array(
				'action'		=> 'Articles',
				'method'		=> 'update',
				'tid'			=> 2,
				'type'			=> 'rpc',
				'data'			=> array(array('data' => array(
					'__bcid'		=> $clientId,
					'id'			=> 1001,
					'title'			=> 'barfoo'
				))),
			),
		));

		// mock a response to not set any headers for real
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		// execute
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData),
			$response,
			array('return' => true)
		));

		// Check that the two responses were returned
		$this->assertCount(2, $responses);
		$this->assertTrue(
			isset($responses[0]->result->data),
			'Expected an result for first request, instead $responses is ' . print_r($responses, true)
		);
		$this->assertEquals('foobar', $responses[0]->result->data->title); // first request should change it to foobar
		$this->assertEquals('barfoo', $responses[1]->result->data->title); // second request should change it to barfoo

		// Read article from database and check if the value is correct.
		$article = ClassRegistry::init('Article');
		$article->recursive = -1;
		$data = $article->read(null, 1001);
		$this->assertEquals('barfoo', $data['Article']['title']);
	}

/**
 * This test ensures that multiple requests, which are sent with multiple requests are executed in the correct order.
 * Thus it ensures that a request with a higher transaction ID is not executed before a request with a lower TID.
 * 
 * See http://bancha.io/documentation-pro-models-consistent-transactions.html#race-conditions
 * 
 * @return void
 */
	public function testEditMultipleRequestsInParallel() {
		$this->skipIf(Configure::read('fastTestsOnly'), 'Skipped slow test case.');
		$this->skipIfSqlite();

		// used fixture:
		// array('id' => 1001, 'title' => 'Title 1', 'published' => true, ...),

		// Execute two requests from same client in parallel.
		$clientId = uniqid();

		$this->_fakeRequest($clientId, 1, 1001, 'foobar', 5, true); // should take longer then then when the second one is called
		sleep(2);
		// user edit again before first change is finished, 
		// because of the sleep time this would be executed BEFORE the first one and therefore the first one would overwrite it.
		// This test should ensure that this isn't happening
		$this->_fakeRequest($clientId, 2, 1001, 'barfoo', 1, true);

		// Wait some seconds until the backround processes are executed.
		sleep(5);

		// first retrieve both requests
		// (therefore no files are left behind if a test fails)
		$responses1 = $this->getAsyncResponse($clientId, 1);
		$responses2 = $this->getAsyncResponse($clientId, 2);

		// Check that the first response returned a result
		$this->asserttrue(is_array($responses1), 'Expected result to be an array '.print_r($responses1,true));
		$this->assertCount(1, $responses1);
		$this->assertTrue(
			isset($responses1[0]->result->data),
			'Expected an result for first request, instead $responses1 is '.print_r($responses1,true)
		);
		$this->assertEquals('foobar', $responses1[0]->result->data->title); // first request should change it to foobar

		// Check that the second response returned a result
		$this->asserttrue(is_array($responses2), 'Expected result to be an array '.print_r($responses1,true));
		$this->assertCount(1, $responses2);
		$this->assertTrue(
			isset($responses2[0]->result->data),
			'Expected an result for first request, instead $responses2 is '.print_r($responses2,true)
		);
		$this->assertEquals('barfoo', $responses2[0]->result->data->title); // second request should change it to barfoo

		// Read article from database and check if the value is correct.
		$article = ClassRegistry::init('Article');
		$article->recursive = -1;
		$data = $article->read(null, 1001);
		$this->assertEquals('barfoo', $data['Article']['title']);
	}

/**
 * Test that a lower TID is never executed after a higher one.
 * 
 * @return void
 */
	public function testEditMultipleRequestsWrongSequence() {
		$this->skipIf(Configure::read('fastTestsOnly'), 'Skipped slow test case.');
		$this->skipIfSqlite();

		// Execute two requests from same client in wrong sequence.
		$clientId = uniqid();

		$this->_fakeRequest($clientId, 7, 1001, 'foobar');
		$this->_fakeRequest($clientId, 5, 1001, 'barfoo'); // this one is old, should not be executed

		// Read article from database and check if the value is correct.
		$article = ClassRegistry::init('Article');
		$article->recursive = -1;
		$data = $article->read(null, 1001);
		$this->assertEquals('foobar', $data['Article']['title']);
	}

}
