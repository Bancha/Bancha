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
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeRequest', 'Network');
App::uses('ArrayConverter', 'Bancha.Bancha/Utility');
App::uses('BanchaRequestTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaRequestCollection. The purpose of this class is to take a POST request (either as $_POST array or as raw POST
 * data), extract batch requests form it into an array of CakeRequest objects.
 *
 * @package    Bancha
 * @subpackage Lib.Network
 */
class BanchaRequestCollection {

/** @var string */
	protected $rawPostData;

/** @var array */
	protected $postData;

/**
 * Constructor.
 *
 * @param string $rawPostData Content of $HTTP_RAW_POST_DATA.
 * @param array $postData Content of $_POST.
 */
	public function __construct($rawPostData = '', $postData = array()) {
		$this->rawPostData = $rawPostData;
		$this->postData = $postData;
	}

/**
 * Returns an array of CakeRequest objects. Performs various transformations on the request passed to the constructor,
 * so that the requests match the format expected by CakePHP.
 *
 * @return array Array with CakeRequest objects.
 */
	public function getRequests() {
		$requests = array();
		// If the request comes from $HTTP_RAW_POST_DATA it could be a batch request.
		if (strlen($this->rawPostData)) {
			$data = json_decode($this->rawPostData, true);
			// TODO: improve detection (not perfect, but should it should be correct in most cases.)
			if (isset($data['action']) || isset($data['method']) || isset($data['data'])) {
				$data = array($data);
			}
			$data = Set::sort($data, '{n}.tid', 'asc');
		} else {
			// Form requests only contain one request.
			$data = array($this->postData);
		}

		if(count($data) > 0) {
	 		for ($i=0; $i < count($data); $i++) {
				$transformer = new BanchaRequestTransformer($data[$i]);

				// CakePHP should think that every Bancha request is a POST request.
				$_SERVER['REQUEST_METHOD'] = 'POST';

				// Create CakeRequest and fill it with values from the transformer.
				$requests[$i] = new CakeRequest($transformer->getUrl());
				
				// the CakeRequest uses the envirement variable $_POST in his
				// during the startup called _processPost() (currently line 153). 
				// This is unclean and adds false data in our case. So delete this data.
				$requests[$i]->data = array();
				
				// now set params for the request
				$requests[$i]['controller'] 	= $transformer->getController();
				$requests[$i]['action']			= $transformer->getAction();
				$requests[$i]['named']			= $transformer->getPaging();
				$requests[$i]['pass']			= $transformer->getPassParams();
				$requests[$i]['plugin']			= null;
				// bancha-specific
				$requests[$i]['tid']			= $transformer->getTid();
				$requests[$i]['extUpload']		= $transformer->getExtUpload();
				$requests[$i]['client_id']		= $transformer->getClientId();
				$requests[$i]['isFormRequest']	= $transformer->isFormRequest();
				$requests[$i]['isBancha']		= true; // additional property for cleaner controller syntax
				
				// Handle all other parameters as POST parameters.
				foreach ($transformer->getCleanedDataArray() as $key => $value) {
					$requests[$i]->data($key, $value);
				}
			}
 		}
		return $requests;
	}

}
