<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Network
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.1.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeResponse', 'Network');
App::uses('BanchaResponseTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaResponseCollection
 *
 * @package       Bancha.Lib.Bancha.Network
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
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
			'method'	=> BanchaResponseTransformer::getMethod($request), // actions are called methods in Ext JS
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
		// only add exception information in debug mode
		if(Configure::read('debug') > 0) {
			$response = array(
				'type'			=> 'exception',
				'exceptionType'	=> get_class($e), // added by Bancha
				'message'		=> $e->getMessage(),
				'where'			=> 'In file "' . $e->getFile() . '" on line ' . $e->getLine() . '.',
				'trace'			=> $e->getTraceAsString(),
			);
		} else {
			$response = array(
				'type'			=> 'exception',
				'message'		=> __("Unknown error."),
			);
		}
		
		// extUpload request exceptions also has to be returns in the html tag, see getResponses()
		if ($request['extUpload'])
		{
			$response['extUpload'] = true;
		}
		$this->responses[] = $response;
		
		return $this;
	 }

/**
 * Combines all CakeResponses into a single response and transforms it into JSON. If it is an formHandler request
 * we format the response as html, see http://www.sencha.com/products/extjs/extdirect
 *
 * @return CakeResponse
 */
	public function getResponses() {
		// If this is an formHandler request with an upload, so wrap the response in a valid HTML body.
		if (isset($this->responses['0']['extUpload']) && $this->responses['0']['extUpload']) {
			return new CakeResponse(array(
				// TODO Is this right implemented? http://www.sencha.com/forum/showthread.php?156689
				'body'		=>	'<html><body><textarea>' . json_encode($this->responses) . '</textarea></body></html>',
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
