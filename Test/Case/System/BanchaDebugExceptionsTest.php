<?php
/**
 * BanchExceptionsTest file.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

require_once dirname(__FILE__) . '/ArticlesController.php';

/**
 * BanchaDebugExceptionsTest. Tests if the Exception to help the user develop are thrown correctly
 *
 * @package       Bancha
 * @category      Tests
 */
class BanchaDebugExceptionsTest extends CakeTestCase {
	
	
	// helper method
	private function getResultForMethod($method,$data=array()) {
		$rawPostData = json_encode(array(
			'action'		=> 'DebugException',
			'method'		=> $method,
			'tid'			=> 1,
			'type'			=> 'rpc',
			'data'			=> $data,
		));
		$dispatcher = new BanchaDispatcher();
		$responses = json_decode($dispatcher->dispatch(
			new BanchaRequestCollection($rawPostData), array('return' => true)
		));
		return $responses;
	}
	
/**
 * When a controller method doesn't return anything, throw an exception.
 * This is implemented in BanchaResponseTransformer::transform()
 */
	public function testNoMethodResultException() {

		$responses = $this->getResultForMethod('getNoResult');
		
		// check exception
		$this->assertEquals('exception', $responses[0]->type);
		$this->assertEquals('CakeException', $responses[0]->exceptionType);
	}
	

}

/**
 * DebugExceptionsController, has many errors a developer can make
 *
 * @package       Bancha
 * @category      TestFixtures
 */
class DebugExceptionsController extends ArticlesController {

/**
 * User forgot to set a return value
 */
	public function getNoResult() {
	}
	
	public function throwNotFoundExceptionMethod($id = null) {
		throw new NotFoundException('Invalid article');
	}
}

