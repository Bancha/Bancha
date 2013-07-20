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
		// Log usage once
		if(!Configure::read('Bancha.isPro') && !Configure::read('Bancha.ServerLogger.logEnvironment')
			&& Cache::read('bancha-logged')==false) {
			Cache::write('bancha-logged', true);
			try {
				$url = 'http://logs.banchaproject.org/';
				$data = array('url'=>$_SERVER['HTTP_HOST'],'path'=>$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
				if (function_exists('curl_init')) {
					$options = array(
						CURLOPT_URL            => $url,
						CURLOPT_HEADER         => true,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_POST           => true,
						CURLOPT_POSTFIELDS     => $data
					);
					$ch = curl_init();
					curl_setopt_array($ch, $options);
					ob_start();
					$response = @curl_exec($ch);
					ob_end_clean();
				} else if (function_exists('stream_context_create')) {
					$stream_options = array(
						'http' => array(
							'method'  => 'POST',
							'content' => $data
					));
					$ctx = stream_context_create($stream_options);
					$response = file_get_contents($url, 0, $ctx);
				}
			} catch(Exception $e) {}
		}

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
