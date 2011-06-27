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
App::uses('BanchaRequestTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaRequestCollection
 *
 * @package bancha.libs
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
	public function __construct($rawPostData = '', $postData = array())
	{
		$this->rawPostData = $rawPostData;
		$this->postData = $postData;
	}

/**
 * Returns an array of CakeRequest objects.
 *
 * @return array Array with CakeRequest objects.
 */
	public function getRequests() {
		$requests = array();
		if (strlen($this->rawPostData))
		{
			$data = json_decode($this->rawPostData, true);
			// TODO: improve detection (not perfect, but should it should be correct in most cases.)
			if (isset($data['action']) || isset($data['method']) || isset($data['data'])) {
				$data = array($data); 
			}
			$data = Set::sort($data, '{n}.tid', 'asc');
		}
		else
		{
			$data = array($this->postData);
		}
		
		if(count($data) > 0) {
	 		for ($i=0; $i < count($data); $i++) {
				$transformer = new BanchaRequestTransformer($data[$i]);
				
				$_SERVER['REQUEST_METHOD'] = 'POST';
				
				$requests[$i] = new CakeRequest($transformer->getUrl());
				$requests[$i]['controller'] = $transformer->getController();
				$requests[$i]['action']		= $transformer->getAction();
				$requests[$i]['named']		= $transformer->getPaging();
				$requests[$i]['pass']		= $transformer->getPassParams();
				
				foreach ($transformer->getCleanedDataArray() as $key => $value) {
					$requests[$i]->data($key, $value);
				}
			}
 		}
		return $requests;
	}

}
