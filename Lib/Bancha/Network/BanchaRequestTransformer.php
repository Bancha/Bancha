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
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('Inflector', 'Utility');
App::uses('BanchaException', 'Bancha.Bancha/Exception');

/**
 * BanchaRequestTranformer.
 *
 * This is a helper class which provides a convenient interface to extract, transform and retrieve data from an Ext JS
 * request in a format suited for CakePHP.
 *
 * @package       Bancha.Lib.Bancha.Network
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
class BanchaRequestTransformer {

/**
 * @var array
 */
	protected $_data;

/**
 * @var string
 */
	protected $_plugin = null;

/**
 * @var string
 */
	protected $_controller = null;

/**
 * @var string
 */
	protected $_model = null;

/**
 * @var string
 */
	protected $_action = null;

/**
 * @var string
 */
	protected $_url = null;

/**
 * @var array
 */
	protected $_paginate = array();

/**
 * True if the given request is a form request.
 * 
 * @var boolean
 */
	protected $_isFormRequest = false;

/**
 * @var boolean
 */
	protected $_tid;

/**
 * True if the given request is an upload request.
 * 
 * @var boolean
 */
	protected $_extUpload;

/**
 * Client ID is a unique ID for every client (= Instance of Ext JS)
 * 
 * @var integer
 */
	protected $_clientId;

/**
 * Constructor. Requires a single Ext JS request in PHP array format.
 *
 * @param array $data Single Ext JS request
 */
	public function __construct(array $data = array()) {
		$this->_data = $data;
	}

/**
 * We want to make sure that php will never throw notices or warning,
 * so when $data = 'string' the result of isset($data['data'][0]) is true,
 * but is_array($data['data'][0]) will throw an php warning.
 *
 * To prohibit that we need a longer check, which we have here.
 *
 * @param Mixed  $variable The variable to look up
 * @param string $path     A path in the style: [data][0][data]
 * @return boolean         True if the path is an array
 */
	public function isArray($variable, $path) {
		$path = substr($path, 1, strlen($path) - 2); // remove first and last char
		$paths = explode('][', $path);

		if ($paths[0] == '') {
			// there is no path
			return is_array($variable);
		}

		// check each path part
		foreach ($paths as $property) {
			if (!isset($variable[$property]) || !is_array($variable[$property])) {
				return false;
			}
			$variable = $variable[$property];
		}

		return true;
	}

/**
 * Returns the name of the plugin.
 *
 * @return string Name of the plugin.
 */
	public function getPlugin() {
		// let getController() do the work
		$this->getController();

		return $this->_plugin;
	}

/**
 * Returns the name of the controller. Thus returns the pluralized value of 'action' from the Ext JS request. Also removes the
 * 'action' property from the Ext JS request.
 *
 * @return string Name of the controller
 * @throws BanchaException If the data does not contain any action value
 */
	public function getController() {
		if (null != $this->_controller) {
			return $this->_controller;
		}

		$controller;
		if (isset($this->_data['action'])) {
			$controller = $this->_data['action'];
			unset($this->_data['action']);
		} elseif (isset($this->_data['extAction'])) {
			$controller = $this->_data['extAction'];
			unset($this->_data['extAction']);
			$this->_isFormRequest = true;
		} else {
			throw new BanchaException('Request does not contain an Ext.Direct action or extAction property.');
		}

		// extract plugin and pluralize controller name
		list($plugin, $controller) = pluginSplit($controller, true);
		$this->_controller = Inflector::pluralize($controller);

		if ($this->_controller === 'Bancha') {
			// special case, the Bancha controller is the Bancha plugin
			$this->_plugin = 'Bancha';
		} else {
			// default case - remove '.' from plugin name
			$this->_plugin = $plugin ? substr($plugin, 0, strlen($plugin) - 1) : null;
		}

		return $this->_controller;
	}

/**
 * Returns true if this is a ExtJS formHandler request
 *
 * @return boolean True if the given request is a form request.
 */
	public function isFormRequest() {
		// let getController() do the work
		$this->getController();

		return $this->_isFormRequest;
	}

/**
 * Returns the name of the expected model. Thus returns the value of 'action' from the Ext JS request.
 *
 * @return string Name of the model.
 */
	public function getModel() {
		if ($this->_model != null) {
			return $this->_model;
		}

		$this->_model = Inflector::singularize($this->getController());
		return $this->_model;
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
		if (null != $this->_action) {
			return $this->_action;
		}
		if (isset($this->_data['method'])) {
			$this->_action = $this->_data['method'];
			unset($this->_data['method']);
		} elseif (isset($this->_data['extMethod'])) {
			$this->_action = $this->_data['extMethod'];
			unset($this->_data['extMethod']);
			$this->isFormRequest = true;
		}

		switch ($this->_action) {
			case 'submit':
				$this->_action = (!empty($this->_data['data']['0']['data']['id']) || !empty($this->_data['id'])) ? 'edit' : 'add';
				break;
			case 'create':
				$this->_action = 'add';
				break;
			case 'update':
				$this->_action = 'edit';
				break;
			case 'destroy':
				$this->_action = 'delete';
				break;
			case 'read':
				$this->_action = (
					($this->isArray($this->_data, '[data][0][data]') && !empty($this->_data['data'][0]['data']['id'])) ||
					($this->isArray($this->_data, '[data][0]') && !empty($this->_data['data'][0]['id'])) ||
					($this->isArray($this->_data, '') && !empty($this->_data['id']))) ? 'view' : 'index';
				break;
		}
		return $this->_action;
	}

/**
 * Returns the extUpload request parameter.
 *
 * @return boolean True if the given request is an upload request
 */
	public function getExtUpload() {
		if (null != $this->_extUpload) {
			return $this->_extUpload;
		}
		$this->_extUpload = isset($this->_data['extUpload']) ? ($this->_data['extUpload'] == 'true') : false; // extjs sends an string
		unset($this->_data['extUpload']);
		return $this->_extUpload;
	}

/**
 * If an URL is provided in the Ext JS request, this method returns it and removes it from the Ext JS request.
 *
 * @return string URL provided in the Ext JS request or NULL if no URL is provided.
 */
	public function getUrl() {
		if (null == $this->_url && isset($this->_data['url'])) {
			$this->_url = $this->_data['url'];
			unset($this->_data['url']);
		}
		return $this->_url;
	}

/**
 * Returns the Transaction ID from the request.
 *
 * @return integer Transaction ID
 */
	public function getTid() {
		if (null != $this->_tid) {
			return $this->_tid;
		}
		if (isset($this->_data['tid'])) {
			$this->_tid = $this->_data['tid'];
			unset($this->_data['tid']);
		} elseif (isset($this->_data['extTID'])) {
			$this->_tid = $this->_data['extTID'];
			unset($this->_data['extTID']);
		}
		return $this->_tid;
	}

/**
 * Returns the Client ID sent by requests as '__bcid' parameter.
 *
 * @return string Unique Client ID or NULL if consistent model is not used.
 */
	public function getClientId() {
		if (null != $this->_clientId) {
			return $this->_clientId;
		}
		if (isset($this->_data['data'][0]['data']['__bcid'])) {
			$this->_clientId = $this->_data['data'][0]['data']['__bcid'];
			unset($this->_data['data'][0]['data']['__bcid']);
		}
		return $this->_clientId;
	}

/**
 * Returns the 'pass' parameters from the Ext JS request. 'pass' parameters are special parameters which are passed
 * directly to the controller/action by CakePHP. The only 'pass' parameter that exist for the CRUD operations is 'id'
 * when the action is 'edit', 'delete' or 'view'. Removes the 'pass' parameters from the Ext JS request.
 *
 * @return array Array with 'pass' parameters
 */
	public function getPassParams() {
		$pass = array();
		if ($this->isArray($this->_data, '[data][0][data]') && isset($this->_data['data'][0]['data']['id'])) {
			// normal requests

			$pass['id'] = $this->_data['data'][0]['data']['id'];
			//unset($this->_data['data'][0]['data']['id']); keep the id in the data as well (otherwise if no data is send the array might not be created)
		} elseif ($this->isArray($this->_data, '[data][0]') && isset($this->_data['data'][0]['id'])) {
			// read requests (actually these are malformed because the ExtJS root/Sencha Touch
			// rootProperty is not set to 'data', but we can ignore this on reads)

			$pass['id'] = $this->_data['data'][0]['id'];
			//unset($this->_data['data'][0]['id']); keep the id in the data as well (otherwise if no data is send the array might not be created)
		} elseif ($this->isFormRequest() && isset($this->_data['id'])) {
			// form upload requests

			$pass['id'] = $this->_data['id'];
			//unset($this->_data['id']); keep the id in the data as well (otherwise if no data is send the array might not be created)
			$this->_isFormRequest = true;
		} elseif (2 === count($this->_data) && isset($this->_data['type']) && 'rpc' == $this->_data['type'] && isset($this->_data['data'])) {
			$pass = $this->_data['data'];
		}
		return $pass;
	}

/**
 * Returns the paging options in a format suited for CakePHP. If a page number is provided by the Ext JS request, it
 * returns this page number directly, otherwise, if provided, it calculates the page number from the start offset and
 * limit. It sets the default value for page to 1 and for limit to 25. This method also transforms the 'sort' array
 * from the Ext JS request.
 *
 * @return array Array with three elements 'page', 'limit' and 'order'
 * @throws BanchaException If a malformed filter value is given
 */
	public function getPaging() {
		if (null != $this->_paginate) {
			return $this->_paginate;
		}

		// find the page and limit
		$page = 1;
		$limit = 500;
		if ($this->isArray($this->_data, '[data][0]')) {
			// the above check needs to be so long, because php allows strigns to be used as array,
			// so to make sure that $this->_data['data'] is not a string we need all from above
			$params = $this->_data['data'][0];

			// find the correct page
			if (isset($params['page'])) {
				$page = $params['page'];
				unset($params['page']);
			} elseif (isset($params['start']) && isset($params['limit'])) {
				$page = floor($params['start'] / $params['limit']);
			}

			// unset this, even if the page was read from the page property
			if (isset($params['start'])) {
				unset($params['start']);
			}

			// find the limit
			if (isset($params['limit'])) {
				$limit = $params['limit'];
				unset($params['limit']);
			}
		}

		// find ordering and direction
		$order = array();
		$sortField = '';
		$direction = '';
		if ($this->isArray($this->_data, '[data][0]') && isset($this->_data['data'][0]['sort'])) {
			foreach ($this->_data['data'][0]['sort'] as $sort) {
				if (isset($sort['property']) && isset($sort['direction'])) {
					$order[$this->getModel() . '.' . $sort['property']] = strtolower($sort['direction']);
					$sortField = $sort['property'];
					$direction = $sort['direction'];
				}
			}
			unset($this->_data['data'][0]['sort']);
		}

		// find store filters
		$conditions = array();
		if ($this->isArray($this->_data, '[data][0][filter]')) {
			$filters = $this->_data['data'][0]['filter'];

			if (!empty($filters) && (!isset($filters[0]) || !is_array($filters[0]))) {
				throw new BanchaException('The supplied filter conditions are not in the default Ext JS/Sencha Touch structure.');
			}

			// collect filter rules
			foreach ($filters as $filter) {
				$conditions[$this->getModel() . '.' . $filter['property']] = $filter['value'];
			}
		}

		$this->_paginate = array(
			'page'			=> $page,
			'limit'			=> $limit,
			'order'			=> $order,
			'sort'			=> $sortField,
			'direction'		=> $direction,
			'conditions'	=> $conditions
		);

		return $this->_paginate;
	}

/**
 * Ext.Direct writes the list of parameters to $data['data'].
 * Transform a Bancha request with model elements to cake structure,
 * otherwise just return the original response.
 *
 * @param string $modelName The model name of the current request
 * @return array            The transformed data
 * @throws BanchaException  If data is not in an expected format
 */
	public function transformDataStructureToCake($modelName) {
		// form uploads save all fields directly in the data array
		if ($this->isFormRequest()) {
			if (isset($this->_data['extType'])) {
				unset($this->_data['extType']);
			}
			return array(
				$modelName => $this->_data
			);
		}

		// for non-form requests

		if ($this->isArray($this->_data, '[data][0][data][0]')) {
			// looks like someone is using the store with batchActions:true
			if (Configure::read('Bancha.allowMultiRecordRequests') != true) {
				throw new BanchaException( // this is not very elegant, till it is not catched by the dispatcher, keep it anyway?
					'You are currently sending multiple records from ExtJS to CakePHP, this is probably because ' .
					'of an store proxy with batchActions:true. Please never batch records on the proxy level ' .
					'(Ext.Direct is batching them). So if you are using a store proxy please set the config ' .
					'batchActions to false.<br /> If you are sending multiple requests by purpose please set ' .
					'<i>Configure::write(\'Bancha.allowMultiRecordRequests\',true)</i> in core.php and you will ' .
					'no longer see this exception.'
				);
			}
			// parse multi-request
			$result = array();
			foreach ($this->_data['data'][0]['data'] as $entry) {
				$result[] = array(
					$modelName => $entry
				);
			}
			$this->_data = $result;
		} elseif ($this->isArray($this->_data, '[data][0][data]')) {
			// this is standard extjs-bancha structure, transform to cake
			$this->_data[$modelName] = $this->_data['data'][0]['data'];
			unset($this->_data['data']);

			// add request doesn't have an id in cake...
			if ($this->getAction() == 'add') {
				// ... so delete it
				unset($this->_data[$modelName]['id']);
			}
		} elseif ($this->isArray($this->_data, '[data]')) {
			// some arbitrary data from ext to just pass through
			$this->_data = $this->_data['data'];
		} else {
			// do data at all given
			$this->_data = array();
		}
		return $this->_data;
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
		return $this->transformDataStructureToCake($this->getModel());
	}

}
