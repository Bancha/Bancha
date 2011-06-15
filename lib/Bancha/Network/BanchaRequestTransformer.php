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

App::uses('ArrayConverter', 'Bancha.Bancha/Utility');

/**
 * BanchaRequestTranformer
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

	public function __construct(array $data = array())
	{
		$this->data = $data;
	}
	
/**
 * Returns the name of the controller.
 *
 * @return string Controller.
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
 * Returns the name of the action.
 *
 * @return string Action
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
	
	public function getUrl()
	{
		if (null == $this->url && isset($this->data['url']))
		{
			$this->url = $this->data['url'];
			unset($this->data['url']);
		}
		return $this->url;
	}
	
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
			$this->getController() => array(
						'page'			=> $page,
						'limit'			=> $limit,
						'order'			=> $order,
			),
		);
		return $this->paginate;
	}
	
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
