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
 * Holds HTTP response statuses
 *
 * @var array
 */
	protected $statusCodes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out'
	);
/**
 * Adds a new CakeResponse object to the response transformer.
 *
 * @param integer $tid Transaction ID
 * @param CakeResponse $response  Cake response object
 * @return BanchaResponseCollection
 */
	public function addResponse($tid, CakeResponse $response, CakeRequest $request, $exception = false) {
		// check statusCode of response 
		
		/** "action":"person",
		//	"method":"update",
		//	"data":[{
		//	"id":"52",
		//	"firstName":"Callie",
		//	"lastName":"Winters",
		//	"street":"Ap ",
		//	"city":"Mayagnez"
		//	}],
		//	"tid":6,
		//	"type":"rpc"
		*/
		
		if ('200' != $response->statusCode() || $exception) {
			$response = array(
				'type'		=> 'exception',
				'tid'		=> $tid,
				'action'	=> $request->controller, // controllers are called action in Ext JS
				'method'	=> $request->action, // actions are called methopds in Ext JS
				'result'	=> $response->body(),
			);
		} else {
			$response = array(
				'type'		=> 'rpc',
				'tid'		=> $tid,
				'action'	=> $request->controller, // controllers are called action in Ext JS
				'method'	=> $request->action, // actions are called methods in Ext JS
				'result'	=> $response->body(),
			);
		}
		
		// Add response to response array.
		$this->responses[] = $response;
		
		return $this;
	}
	
	public function addException($tid, Exception $e, CakeRequest $request) {
		$response = new CakeResponse();
		// values
		$response->body($e->getMessage());
		$this->addResponse($tid, $response, $request, true);
	 }
	 
/**
 * Combines all CakeResponses into a single response and transforms it into JSON.
 *
 * @return CakeResponse
 */
	public function getResponses() {
		$responses = array();
		foreach ($this->responses as $singleResponse)
		{
			$responses[] = $singleResponse;
		}
		
		return new CakeResponse(array(
			'body'			=> json_encode($responses),
			'status'		=> 200,
			'type'			=> 'json',
			'charset'		=> 'utf-8',
		));
	}

}
