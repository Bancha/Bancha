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
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeResponse', 'Network');

/**
 * BanchaResponse
 *
 * @package bancha.libs
 */

class BanchaResponse extends CakeResponse {
	
	/** @var array */
	protected $responses = array();

	public function addResponse(CakeResponse $response) {
		// TODO: EXCEPTIONS
		// check statusCode of response 
		if ($response->statusCode() != "200") {
			$response = array(
			    "success" => false,
			    "message" => $this->_statusCodes[$response->statusCode()],
			    "data" => $response->body()
			);
		} else {
			$response = array(
			    "success" => true,
			    "message" => $this->_statusCodes[$response->statusCode()],
			    "data" => $response->body()
			);
		}
		
		array_push($this->responses, $response);
		
		return $this;
	}
	
	public function getResponses() {
		if (isset($this->_headers['Location']) && $this->_status === 200) {
			$this->statusCode(302);
		}
		
		return $this->responses;
	}
	
	public function send() {
		foreach ($this->responses as $value) {
			// echo '{"sample":'.json_encode($value).'}';
			
			// transform CakeResponse into ExtJs JSON
			// Cakephp: array with fields (key value)
			// ExtJs: Columns are a list of arrays, each array begins with key name -> and value of the columnname
			// check what ext wants and push it with JSON ENCODE
			
			$cakeResponse = new CakeResponse(array(
				'body'		=> json_encode($value),
				'status'	=> "200",
				'type'		=> 'json',
				'charset'	=> "UTF-8")
			);
			$cakeResponse->send($cakeResponse);
		}
	}

}
