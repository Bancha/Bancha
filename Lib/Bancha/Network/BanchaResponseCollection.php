<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.Network
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeResponse', 'Network');
App::uses('BanchaResponseTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaResponseCollection
 *
 * @package    Bancha
 * @subpackage Lib.Network
 */
class BanchaResponseCollection {

/** @var array List of responses. */
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
		$response = array(
			'type'		=> 'rpc',
			'tid'		=> $tid,
			'action'	=> Inflector::singularize($request->controller), // controllers are called action in Ext JS
			'method'	=> $request->action, // actions are called methods in Ext JS
			'result'	=> BanchaResponseTransformer::transform($response->body(), $request),
		);
		if ($request['extUpload'])
		{
			$response['extUpload'] = true;
		}
		$this->responses[] = $response;

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
			'trace'		=> $e->getTraceAsString(),
		);

		return $this;
	 }

/**
 * Combines all CakeResponses into a single response and transforms it into JSON. If the first response does not contain
 * an array, we assume that 'extUpload' is active and therefore we do not transform the response into JSON.
 *
 * @return CakeResponse
 */
	public function getResponses() {
		// Response to ExtUpload request
		if (isset($this->responses[0]['extUpload']) && $this->responses[0]['extUpload']) {
			return new CakeResponse(array(
				'body'		=>	$this->responses[0]['result'],
				'status'	=> 200,
				'type'		=> 'text/html',
				'charset'	=> 'utf-8',
			));
		}
		return new CakeResponse(array(
			'body'			=> json_encode($this->responses),
			'status'		=> 200,
			'type'			=> 'json',
			'charset'		=> 'utf-8',
		));
	}

}
