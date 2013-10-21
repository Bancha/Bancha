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

App::uses('CakeRequest', 'Network');
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

/**
 * Response to use as result
 * @var CakeResponse
 */
	protected $_CakeResponse = null;

/**
 * List of sub-responses
 * @var CakeResponse[]
 */
	protected $_responses = array();

/**
 * Constructor
 */
	public function __construct(CakeResponse $CakeResponse) {
		$this->_CakeResponse = $CakeResponse;
	}

/**
 * Adds a new CakeResponse object to the response collection.
 *
 * @param integer $tid Transaction ID
 * @param CakeResponse $CakeResponse Cake response object
 * @param CakeRequest $CakeRequest CakeRequest object
 * @return BanchaResponseCollection $this
 */
	public function addResponse($tid, CakeResponse $CakeResponse, CakeRequest $CakeRequest) {
		$response = array(
			'type'		=> 'rpc',
			'tid'		=> $tid,
			'action'	=> ($CakeRequest->plugin ? $CakeRequest->plugin.'.' : '').Inflector::singularize($CakeRequest->controller), // controllers are called action in Ext JS
			'method'	=> BanchaResponseTransformer::getMethod($CakeRequest), // actions are called methods in Ext JS
			'result'	=> BanchaResponseTransformer::transform($CakeResponse->body(), $CakeRequest),
		);
		if ($CakeRequest['extUpload']) {
			$response['extUpload'] = true;
		}
		$this->_responses[] = $response;

		return $this;
	}

/**
 * Adds an exception to the BanchaResponse
 *
 * @param integer $tid Transaction ID
 * @param Exception $e Exception
 * @param CakeRequest $CakeRequest CakeRequest object.
 * @return void
 */
	public function addException($tid, Exception $e, CakeRequest $CakeRequest) {
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
		if ($CakeRequest['extUpload']) {
			$response['extUpload'] = true;
		}
		$this->_responses[] = $response;

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


		// request was successfull
		$this->_CakeResponse->statusCode(200);
		$this->_CakeResponse->charset('utf-8');

		// If this is an formHandler request with an upload, so wrap the response in a valid HTML body.
		if (isset($this->_responses['0']['extUpload']) && $this->_responses['0']['extUpload']) {
			$this->_CakeResponse->type('text/html');
			// TODO Is this right implemented? http://www.sencha.com/forum/showthread.php?156689
			$this->_CakeResponse->body('<html><body><textarea>' . json_encode($this->_responses) . '</textarea></body></html>');
		} else {
			$this->_CakeResponse->type('json');
			$this->_CakeResponse->body(json_encode($this->_responses));
		}

		return $this->_CakeResponse;
	}

}
