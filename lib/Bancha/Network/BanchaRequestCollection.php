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

/**
 * BanchaRequestCollection
 *
 * @package bancha.libs
 */
class BanchaRequestCollection {

/**
 * Returns an array of CakeRequest objects.
 *
 * @return array Array with CakeRequest objects.
 */
	public function getRequests() {
		// TODO: Implement BanchaRequestCollection::getRequest()
		/* The idea of this method is to somehow iterate/parse the request from Ext JS and create a CakeRequest object.
		   BanchaDispatcher::dispatch() will take the array of CakeRequest objects returned by this method and call
		   Dispatcher::dispatch() for every CakeRequest object. Thus the default CakePHP dispatching process is
		   executed.
		*/
		$requests = array();
		 
		$json_data = $_POST;
		$data = json_decode($json_data, true);
		// check ob nur 1 request ist
		if ($data['action'] != null) {
			$data = array($data); 
		} 
		
		if(count($data) > 0) {
	 		for ($i=0; $i < count($data); $i++) {
				$_POST = $data[$i];
				$url = null;
				if (isset($_POST['url']))
				{
					$url = $_POST['url'];
				}
				$requests[$i] = new CakeRequest($url);
				foreach ($data[$i] as $key => $value) {
					$requests[$i]->data($key, $value);
				}
			}
 		}
		return $requests;
	}
	
}
