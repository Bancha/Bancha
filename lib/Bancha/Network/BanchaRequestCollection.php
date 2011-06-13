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
				$requests[$i] = new CakeRequest($url);
				$requests[$i]['controller'] = $data[$i]['controller'];
				$requests[$i]['action']		= $data[$i]['action'];
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
