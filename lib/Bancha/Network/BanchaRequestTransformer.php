<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Inflector', 'Utility');
App::uses('ArrayConverter', 'Bancha.Bancha/Utility');

/**
 * BanchaRequestTranformer.
 *
 * This is a helper class which provides a convenient interface to extract, transform and retrieve data from an Ext JS
 * request in a format suited for CakePHP.
 *
 * @package bancha.libs
 */
class BanchaRequestTransformer {
	
/** @var array */
	private $data;
	
/** @var string */
	protected $controller = null;
	
/** @var string */
	protected $action = null;
	
/** @var string */
	protected $url = null;
	
/** @var array */
	protected $paginate = array();

/**
 * Constructor. Requires a single Ext JS request in PHP array format.
 *
 * @param array $data Single Ext JS request
 */
	public function __construct(array $data = array())
	{
		$this->data = $data;
	}
	
/**
 * Returns the name of the controller. Thus returns the value of 'action' from the Ext JS request. Also removes the
 * 'action' property from the Ext JS request.
 *
 * @return string Name of the controller.
 */
	public function getController()
	{
		if (null == $this->controller && isset($this->data['action']))
		{
			$this->controller = $this->data['action'];
			unset($this->data['action']);
		}
		return $this->controller;
	}
	
/**
 * Returns the name of the action. Thus returns the value of 'method' from the Ext JS request. Because Ext JS and
 * CakePHP use different names for CRUD operations, this method also transforms it according to the following list:
 * - create -> add
 * - update -> edit
 * - destroy -> delete
 * - read -> view (if an ID is provided in the Data array).
 * - read -> index (if no ID is provided in the Data array).
 * This method also removes the 'method' property from the Ext JS request.
 *
 * @return string Name of the action.
 */
	public function getAction()
	{
		if (null == $this->action && isset($this->data['method']))
		{
			switch ($this->data['method'])
			{
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
					$this->action = isset($this->data['data']['id']) ? 'view' : 'index';
					break;
				default:
					$this->action = $this->data['method'];
					break;
			}
			unset($this->data['method']);
		}
		return $this->action;
	}
	
/**
 * If an URL is provided in the Ext JS request, this method returns it and removes it from the Ext JS request.
 *
 * @return string URL provided in the Ext JS request or NULL if no URL is provided.
 */
	public function getUrl()
	{
		if (null == $this->url && isset($this->data['url']))
		{
			$this->url = $this->data['url'];
			unset($this->data['url']);
		}
		return $this->url;
	}
	
/**
 * Returns the 'pass' parameters from the Ext JS request. 'pass' parameters are special parameters which are passed
 * directly to the controller/action by CakePHP. The only 'pass' parameter that exist for the CRUD operations is 'id'
 * when the action is 'edit', 'delete' or 'view'. Removes the 'pass' parameters from the Ext JS request.
 *
 * @return array Array with 'pass' parameters
 */
	public function getPassParams()
	{
		$pass = array();
		if (in_array($this->getAction(), array('edit', 'delete', 'view')) && isset($this->data['data']['id']))
		{
			$pass['id'] = $this->data['data']['id'];
			unset($this->data['data']['id']);
		}
		return $pass;
	}
	
/**
 * Returns the paging options in a format suited for CakePHP. If a page number is provided by the Ext JS request, it
 * returns this page number directly, otherwise, if provided, it calculates the page number from the start offset and
 * limit. It sets the default value for page to 1 and for limit to 25. This method also transforms the 'sort' array
 * from the Ext JS request.
 *
 * @return void
 * @author Florian Eckerstorfer
 */
	public function getPaging()
	{
		if (null != $this->paginate)
		{
			return $this->paginate;
		}
		
		$page = 1;
		if (isset($this->data['data']['page']))
		{
			$page = $this->data['data']['page'];
			unset($this->data['data']['page']);
		}
		else if (isset($this->data['data']['start']) && isset($this->data['data']['limit']))
		{
			$page = floor($this->data['data']['start'] / $this->data['data']['limit']);
			unset($this->data['data']['start']);
		}
		$limit = 25;
		if (isset($this->data['data']['limit']))
		{
			$limit = $this->data['data']['limit'];
			unset($this->data['data']['limit']);
		}
		$order = array();
		if (isset($this->data['data']['sort']))
		{
			foreach ($this->data['data']['sort'] as $sort)
			{
				if (isset($sort['property']) && isset($sort['direction']))
				{
					$order[$this->getController() . '.' . $sort['property']] = strtolower($sort['direction']);
				}
			}
			unset($this->data['data']['sort']);
		}
		$this->paginate = array(
			Inflector::singularize($this->getController()) => array(
						'page'			=> $page,
						'limit'			=> $limit,
						'order'			=> $order,
			),
		);
		return $this->paginate;
	}
	
/**
 * Returns the data array from the Ext JS request without all special elements. Therefore it calls all the get*() 
 * methods in the class, which not only return the values but also clean the request.
 *
 * @return array Cleaned data array.
 */
	public function getCleanedDataArray()
	{
		$data = array();
		// Call get*() methods to clean data array
		$this->getController();
		$this->getAction();
		$this->getUrl();
		$this->getPassParams();
		$this->getPaging();
		if (isset($this->data['data']))
		{
			$data = $this->data['data'];
		}
		return $data;
	}

}
