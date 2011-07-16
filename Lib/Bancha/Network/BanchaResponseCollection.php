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

App::uses('CakeResponse', 'Network');

/**
 * BanchaResponseCollection
 *
 * @package bancha.libs
 */
class BanchaResponseCollection {
	
/** @var array */
	protected $responses = array();


/**
 * Adds a new CakeResponse object to the response collection.
 *
 * @param integer $tid Transaction ID
 * @param CakeResponse $response Cake response object
 * @param CakeRequest $request CakeRequest object
 * @param boolean $exception TRUE if the response is an exception.
 * @return BanchaResponseCollection
 */
	public function addResponse($tid, CakeResponse $response, CakeRequest $request, $exception = false) {
		$this->responses[] = array(
			'type'		=> 'rpc',
			'tid'		=> $tid,
			'action'	=> $request->controller, // controllers are called action in Ext JS
			'method'	=> $request->action, // actions are called methods in Ext JS
			'result'	=> $response->body(),
		);
		
		return $this;
	}
	
/**
 * Adds an exception to the BanchaResponse
 *
 * @param integer $tid Transaction ID
 * @param Exception $e Exception
 * @param CakeRequest $request CakeRequest object.
 * @return void
 */
	public function addException($tid, Exception $e, CakeRequest $request) {
		$this->responses[] = array(
			'type'		=> 'exception',
			'message'	=> $e->getMessage(),
			'where'		=> 'In file "' . $e->getFile() . '" on line ' . $e->getLine() . '.',
		);
		
		return $this;
	 }
	 
/**
 * Combines all CakeResponses into a single response and transforms it into JSON.
 *
 * @return CakeResponse
 */
	public function getResponses() {
		return new CakeResponse(array(
			'body'			=> json_encode($this->responses),
			'status'		=> 200,
			'type'			=> 'json',
			'charset'		=> 'utf-8',
		));
	}

}
