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
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('CakeRequest', 'Network');
App::uses('ArrayConverter', 'Bancha.Bancha/Utility');

/**
 * BanchaRequestCollection
 *
 * @package bancha.libs
 */
class BanchaRequestCollection {
	
	/** @var string */
	protected $rawPostData;
	
	/**
	 * Constructor.
	 *
	 * @param string $rawPostData Content of $HTTP_RAW_POST_DATA
	 */
	public function __construct($rawPostData)
	{
		$this->rawPostData = $rawPostData;
	}

/**
 * Returns an array of CakeRequest objects.
 *
 * @return array Array with CakeRequest objects.
 */
	public function getRequests() {
		$requests = array();
		$data = json_decode($this->rawPostData, true);
		
		// TODO: improve detection (not perfect, but should it should be correct in most cases.)
		if (isset($data['action']) || isset($data['method']) || isset($data['data'])) {
			$data = array($data); 
		} 
		
		if(count($data) > 0) {
	 		for ($i=0; $i < count($data); $i++) {
				$converter = new ArrayConverter($data[$i]);
				$url = $converter->removeElement('url');
				$converter->renameElement('action', 'controller')
						  ->renameElement('method', 'action')
						  ->changeValue('action', 'create', 'add')
						  ->changeValue('action', 'update', 'edit')
						  ->changeValue('action', 'destroy', 'delete')
						  ->changeValue('action', 'read', 'index');
				$data[$i] = $converter->getArray();
				
				// if action == index && isset(id) --> view action
				if ('index' == $data[$i]['action'] && isset($data[$i]['data']['id']))
				{
					$data[$i]['action'] = 'view';
				}
				
				$pass = array();
				if ('edit' == $data[$i]['action'] || 'delete' == $data[$i]['action'] || 'view' == $data[$i]['action'])
				{
					$pass['id'] = $data[$i]['data']['id'];
					unset($data[$i]['data']['id']);
				}
				
				// build pagination
				$controller = $data[$i]['controller'];
				$page = 1;
				if (isset($data[$i]['data']['page']))
				{
					$page = $data[$i]['data']['page'];
					unset($data[$i]['data']['page']);
				}
				else if (isset($data[$i]['data']['start']) && isset($data[$i]['data']['limit']))
				{
					$page = floor($data[$i]['data']['start'] / $data[$i]['data']['limit']);
					unset($data[$i]['data']['start']);
				}
				$limit = 25;
				if (isset($data[$i]['data']['limit']))
				{
					$limit = $data[$i]['data']['limit'];
					unset($data[$i]['data']['limit']);
				}
				$order = array();
				if (isset($data[$i]['data']['sort']))
				{
					foreach ($data[$i]['data']['sort'] as $sortOption)
					{
						if (isset($sortOption['property']) && isset($sortOption['direction']))
						{
							$order[$controller . '.' . $sortOption['property']] = strtolower($sortOption['direction']);
						}
					}
					unset($data[$i]['data']['sort']);
				}
				$pagination = array(
					$controller		=> array(
						'page'			=> $page,
						'limit'			=> $limit,
						'order'			=> $order,
					),
				);
				
				$requests[$i] = new CakeRequest($url);
				$requests[$i]['controller'] = $data[$i]['controller'];
				$requests[$i]['action']		= $data[$i]['action'];
				$requests[$i]['named']		= null;
				$requests[$i]['pass']		= $pass;
				$requests[$i]['paging'] = $pagination;
				if (isset($data[$i]['data'])) {
					foreach ($data[$i]['data'] as $key => $value) {
						$requests[$i]->data($key, $value);
					}
				}
			}
 		}
		return $requests;
	}

}
