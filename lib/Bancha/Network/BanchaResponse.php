<?php
App::uses('CakeResponse', 'Network');
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

/**
 * BanchaResponse
 *
 * @package bancha.libs
 */

class BanchaResponse extends CakeResponse
{
	public $responses = array();
	// TODO: eventuell Konstruktor überschreiben ? sinnvoll?
	// TODO: EXCEPTIONS BEHANDELN
	// TODO: Beachten, als was der Response hineinkommt (Object / String->fehler?)
	public function addResponse(CakeResponse $response)
	{			
		// überprüfen, welchen statuscode der response hat 
		if ($response->statusCode() != "200") {
			$response = array(
			    "success" => false,
			    "message" => $this->_statusCodes[$response->statusCode()],
			    "data" => $response
			);	
		} else {
			$response = array(
			    "success" => true,
			    "message" => $this->_statusCodes[$response->statusCode()],
			    "data" => $response
			);
		}
		// TODO: CakeResponse umformen in ExtJs fähiger
		// Cakephp: array mit fields (key value)
		// ExtJs: Spalten sind eine liste von arrays, jedes array beginnt mit key name -> und value den spaltennamen.
		// herausfinden, was extjs wirklich haben will und so pushen.
		
		array_push($this->responses, $response);
	}
	
	public function getResponses()
	{
		// TODO: implement (??, maybe overwrite variables)
		if (isset($this->_headers['Location']) && $this->_status === 200) {
			$this->statusCode(302);
		}
		
		//$codeMessage = $this->_statusCodes[$this->_status];
		
		//$this->_sendHeader("{$this->_protocol} {$this->_status} {$codeMessage}");
		//$this->_sendHeader('Content-Type', "{$this->_contentType}; charset={$this->_charset}");

		//foreach ($this->_headers as $header => $value) {
			//$this->_sendHeader($header, $value);
		//}
		return $this->responses;
	}
}
