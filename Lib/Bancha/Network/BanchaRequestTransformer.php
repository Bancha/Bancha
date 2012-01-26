<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.Network
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('Inflector', 'Utility');
App::uses('ArrayConverter', 'Bancha.Bancha/Utility');

/**
 * BanchaRequestTranformer.
 *
 * This is a helper class which provides a convenient interface to extract, transform and retrieve data from an Ext JS
 * request in a format suited for CakePHP.
 *
 * @package    Bancha
 * @subpackage Lib.Network
 */
class BanchaRequestTransformer {

/** @var array */
	private $data;

/** @var string */
	protected $controller = null;

/** @var string */
	protected $model = null;

/** @var string */
	protected $action = null;

/** @var string */
	protected $url = null;

/** @var array */
	protected $paginate = array();

/** @var boolean TRUE if the given request is a form request. */
	protected $isFormRequest = false;

/** @var boolean */
	protected $tid;

/** @var boolean TRUE if the given request is an upload request. */
	protected $extUpload;

/** @var integer Client ID is a unique ID for every client (= Instance of Ext JS) */
	protected $client_id;

/**
 * Constructor. Requires a single Ext JS request in PHP array format.
 *
 * @param array $data Single Ext JS request
 */
	public function __construct(array $data = array()) {
		$this->data = $data;
	}

/**
 * Returns the name of the controller. Thus returns the pluralized value of 'action' from the Ext JS request. Also removes the
 * 'action' property from the Ext JS request.
 *
 * @return string Name of the controller.
 */
	public function getController() {
		if (null != $this->controller)
		{
			return $this->controller;
		}
		if (isset($this->data['action']))
		{
			$this->controller = Inflector::pluralize($this->data['action']);
			unset($this->data['action']);
		}
		else if (isset($this->data['extAction']))
		{
			$this->controller = Inflector::pluralize($this->data['extAction']);
			unset($this->data['extAction']);
			$this->isFormRequest = true;
		}
		return $this->controller;
	}
	
	/**
	 * Returns true if this is a ExtJS formHandler request
	 */
	public function isFormRequest() {
		// let getController() do the work
		$this->getController();
		
		return $this->isFormRequest;
	}
	
	/**
	 * Returns the name of the expected model. Thus returns the value of 'action' from the Ext JS request. 
	 *
	 * @return string Name of the model.
	 */
	public function getModel() {
		if (null != $this->model)
		{
			return $this->controller;
		}
		
		$this->model = Inflector::singularize($this->getController());
		return $this->model;
	}

/**
 * Returns the name of the action. Thus returns the value of 'method' from the Ext JS request. Because Ext JS and
 * CakePHP use different names for CRUD operations, this method also transforms it according to the following list:
 * - create    -> add
 * - update    -> edit
 * - destroy   -> delete
 * - read      -> view (if an ID is provided in the Data array).
 * - read      -> index (if no ID is provided in the Data array).
 * - submit    -> add (if no ID is provided in the Data array).
 * - submit    -> edit (if an ID is provided in the Data array).
 * This method also removes the 'method' property from the Ext JS request.
 *
 * @return string Name of the action.
 */
	public function getAction() {
		if (null != $this->action) {
			return $this->action;
		}
		if (isset($this->data['method'])) {
			$this->action = $this->data['method'];
			unset($this->data['method']);
		} else if (isset($this->data['extMethod'])) {
			$this->action = $this->data['extMethod'];
			unset($this->data['extMethod']);
			$this->isFormRequest = true;
		}

		switch ($this->action) {
			case 'submit':
				$this->action = (!empty($this->data['data']['0']['data']['id']) || !empty($this->data['id'])) ? 'edit' : 'add';
				break;
			case 'create':
				$this->action = 'add';
				break;
			case 'update':
				$this->action = 'edit';
				break;
			case 'destroy':
				$this->action = 'delete';
				break;
			case 'read':
				$this->action = (!empty($this->data['data']['0']['data']['id']) || !empty($this->data['id'])) ? 'view' : 'index';
				break;
		}
		return $this->action;
	}

/**
 * Returns the extUpload request parameter.
 *
 */
	public function getExtUpload() {
		if (null != $this->extUpload) {
			return $this->extUpload;
		}
		$this->extUpload = isset($this->data['extUpload']) ? $this->data['extUpload'] : false;
		unset($this->data['extUpload']);
		return $this->extUpload;
	}

/**
 * If an URL is provided in the Ext JS request, this method returns it and removes it from the Ext JS request.
 *
 * @return string URL provided in the Ext JS request or NULL if no URL is provided.
 */
	public function getUrl() {
		if (null == $this->url && isset($this->data['url'])) {
			$this->url = $this->data['url'];
			unset($this->data['url']);
		}
		return $this->url;
	}

/**
 * Returns the Transaction ID from the request.
 *
 * @return integer Transaction ID
 */
	public function getTid() {
		if (null != $this->tid) {
			return $this->tid;
		}
		if (isset($this->data['tid'])) {
			$this->tid = $this->data['tid'];
			unset($this->data['tid']);
		} else if (isset($this->data['extTID'])) {
			$this->tid = $this->data['extTID'];
			unset($this->data['extTID']);
		}
		return $this->tid;
	}

/**
 * Returns the Client ID sent by requests as '__bcid' parameter.
 *
 * @return string Unique Client ID or NULL if consistent model is not used.
 */
	public function getClientId() {
		if (null != $this->client_id) {
			return $this->client_id;
		}
		if (isset($this->data['data'][0]['data']['__bcid'])) {
			$this->client_id = $this->data['data'][0]['data']['__bcid'];
			unset($this->data['data'][0]['data']['__bcid']);
		}
		return $this->client_id;
	}

/**
 * Returns the 'pass' parameters from the Ext JS request. 'pass' parameters are special parameters which are passed
 * directly to the controller/action by CakePHP. The only 'pass' parameter that exist for the CRUD operations is 'id'
 * when the action is 'edit', 'delete' or 'view'. Removes the 'pass' parameters from the Ext JS request.
 *
 * @return array Array with 'pass' parameters
 */
	public function getPassParams() {
		// normal requests
		if (isset($this->data['data'][0]['data']['id'])) {
			$pass['id'] = $this->data['data'][0]['data']['id'];
			unset($this->data['data'][0]['data']['id']);
		// form upload requests
		} else if ($this->isFormRequest() && isset($this->data['id'])) {
			$pass['id'] = $this->data['id'];
			unset($this->data['id']);
			$this->isFormRequest = true;
		} else {
			$pass = $this->transformDataStructureToCake($this->getModel(), $this->data);
		}
		return $pass;
	}

/**
 * Returns the paging options in a format suited for CakePHP. If a page number is provided by the Ext JS request, it
 * returns this page number directly, otherwise, if provided, it calculates the page number from the start offset and
 * limit. It sets the default value for page to 1 and for limit to 25. This method also transforms the 'sort' array
 * from the Ext JS request.
 *
 * @return array Array with three elements 'page', 'limit' and 'order'.
 */
	public function getPaging() {
		if (null != $this->paginate) {
			return $this->paginate;
		}
		
		$page = 1;

		if (isset($this->data['data'][0]) && is_array($this->data['data'][0]) && isset($this->data['data'][0]['page'])) {
			$page = $this->data['data'][0]['page'];
			unset($this->data['data'][0]['page']);
		} else if (isset($this->data['data'][0]) && is_array($this->data['data'][0]) && isset($this->data['data'][0]['start']) && isset($this->data['data'][0]['limit'])) {
			$page = floor($this->data['data'][0]['start'] / $this->data['data'][0]['limit']);
			unset($this->data['data'][0]['start']);
		}
		$limit = 500;
		if (isset($this->data['data'][0]) && is_array($this->data['data'][0]) && isset($this->data['data'][0]['limit'])) {
			$limit = $this->data['data'][0]['limit'];
			unset($this->data['data'][0]['limit']);
		}
		$order = array();
		if (isset($this->data['data'][0]) && is_array($this->data['data'][0]) && isset($this->data['data'][0]['sort'])) {
			foreach ($this->data['data'][0]['sort'] as $sort) {
				if (isset($sort['property']) && isset($sort['direction'])) {
					$order[$this->getController() . '.' . $sort['property']] = strtolower($sort['direction']);
				}
			}
			unset($this->data['data'][0]['sort']);
		}
		$this->paginate = array(
			'page'			=> $page,
			'limit'			=> $limit,
			'order'			=> $order,
		);
		return $this->paginate;
	}
	
	/**
	 * Transform a Bancha request with one data element to cake structure
	 * otherwise just return the original response.
	 * This function has no side-effects.
	 *
	 * @param $modelName The model name of the current request
	 * @param $data The input request data from Bancha-ExtJS
	 */
	public function transformDataStructureToCake($modelName,$data) {
		
		// form uploads save all fields directly in the data array
		if($this->isFormRequest()) {
			if(isset($data['extType'])) {
				unset($data['extType']);
			}
			return array(
				$modelName => $data
			);
		}
		
		// non-form request
		if( isset($data['data'][0]['data']) && !isset($data['data'][0]['data'][0])) {
			// this is standard extjs-bancha structure, transform to cake
			$data = array(
				$modelName => $data['data'][0]['data']
			);
			// add request doesn't have an id in cake...
			if($this->getAction()=='add') {
				// ... so delete it
				unset($data[$modelName]['id']);
			}
		} else if( isset($data['data'][0]['data'][0]) && is_array($data['data'][0]['data'][0])) {
			// looks like someone is using the store with batchActions:true
			if(Configure::read('Bancha.allowMultiRecordRequests') != true) {
				throw new CakeException( // this is not very elegant, till it is not catched by the dispatcher, keep it anyway?
					'You are currently sending multiple records from ExtJS to CakePHP, this is probably because '.
					'of an store proxy with batchActions:true. Please never batch records on the proxy level '.
					'(Ext.Direct is batching them). So if you are using a store proxy please set the config '.
					'batchActions to false.<br /> If you are sending multiple requests by purpose please set '.
					'<i>Configure::write(\'Bancha.allowMultiRecordRequests\',true)</i> in core.php and you will '.
					'no longer see this exception.');
			}
			// parse multi-request
			$result = array();
			foreach($data['data'][0]['data'] as $entry) {
				$result[] = array(
					$modelName => $entry
				);
			}
			$data = $result;
			
		} else if( isset($data['data'])) {
			// some data from ext to just pass through
			$data = $data['data'];
		} else {
			$data = array();
		}
		return $data;
	}
/**
 * Returns the data array from the Ext JS request without all special elements. Therefore it calls all the get*()
 * methods in the class, which not only return the values but also clean the request.
 *
 * @return array Cleaned data array.
 */
	public function getCleanedDataArray() {
		// Call get*() methods to clean data array
		$this->getController();
		$this->getAction();
		$this->getUrl();
		$this->getClientId();
		$this->getExtUpload();
		$this->getPassParams();
		$this->getPaging();
		
		// prepare and return data
		return $this->transformDataStructureToCake($this->getModel(), $this->data);
	}

}
