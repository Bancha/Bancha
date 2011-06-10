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

class BanchaResponseTransformer {
	
	/** @var array */
	protected $responses = array();

/**
 * Adds a new CakeResponse object to the response transformer.
 *
 * @param CakeResponse $response 
 * @return BanchaResponseTransformer
 */
	public function addResponse(CakeResponse $response) {
		// TODO: EXCEPTIONS
		// check statusCode of response 
		if ($response->statusCode() != "200") {
			$response = array(
			    "success" => false,
			    "message" => $response->statusCode(),
			    "data" => $response->body()
			);
		} else {
			$response = array(
			    "success" => true,
			    "message" => $response->statusCode(),
			    "data" => $response->body()
			);
		}
		
		array_push($this->responses, $response);
		
		return $this;
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
