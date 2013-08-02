<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Network
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
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
 * @package       Bancha.Lib.Bancha.Network
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
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
		$this->rawPostData = $rawPostData; // when the enctype is "multipart/form-data", the rawPostData will be empty
		$this->postData = $postData; // so we need the $_POST data as well for form submits with file uploads
	}

/**
 * Returns an array of CakeRequest objects. Performs various transformations on the request passed to the constructor,
 * so that the requests match the format expected by CakePHP.
 *
 * @return array Array with CakeRequest objects.
 */
	public function getRequests() {

		if(isset($this->postData) && isset($this->postData['extTID'])) {
			// this is a form request, form request data is directly avialable
			// in the $postData and only contains one request.
			$data = array($this->postData); // make an array of requests data

		} else if(strlen($this->rawPostData)) {
			// It is a normal Ext.Direct request, payload is read from php://input (saved in $rawPostData)
			$data = json_decode($this->rawPostData, true);
			if($data === NULL) {
				// payload could not be converted, probably misformed json
				throw new BanchaException(
					'Misformed Input: The Bancha Dispatcher expected a json string, instead got ' . $this->rawPostData);
			}
			if (isset($data['action']) || isset($data['method']) || isset($data['data'])) {
				// this is just a single request, so make an array of requests data
				$data = array($data);
			}
			// make sure that we keep the set in order
			$data = Set::sort($data, '{n}.tid', 'asc');
		} else {
			// no data passed
			throw new BanchaException(
				'Missing POST Data: The Bancha Dispatcher expected to get all requests in the Ext.Direct format as POST '.
				'parameter, but there is no data in this request. You can not access this site directly!');
		}

		$requests = array();
		if(count($data) > 0) {
	 		for ($i=0; $i < count($data); $i++) {
				$transformer = new BanchaRequestTransformer($data[$i]);

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
				$requests[$i]['plugin']			= $requests[$i]['controller']=='Bancha' ? 'Bancha' : null;
				// bancha-specific
				$requests[$i]['tid']			= $transformer->getTid();
				$requests[$i]['extUpload']		= $transformer->getExtUpload();
				$requests[$i]['client_id']		= $transformer->getClientId();
				$requests[$i]['isFormRequest']	= $transformer->isFormRequest();
				$requests[$i]['pass']			= $transformer->getPassParams();
				// additional property for cleaner controller syntax
				$requests[$i]['isBancha']		= true;

				// Handle all other parameters as POST parameters.
				foreach ($transformer->getCleanedDataArray() as $key => $value) {
					$requests[$i]->data($key, $value);
				}
			}
 		}
		return $requests;
	}

}
