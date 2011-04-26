<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

/**
 * BanchaRequest
 *
 * @package bancha.libs
 */
class BanchaRequest
{

	/**
	 * Returns an array of CakeRequest objects.
	 *
	 * @return array Array with CakeRequest objects.
	 */
	public function getRequests()
	{
		// TODO: Implement BanchaRequest::getRequest()
		/* The idea of this method is to somehow iterate/parse the request from Ext JS and create a CakeRequest object.
		   BanchaDispatcher::dispatch() will take the array of CakeRequest objects returned by this method and call
		   Dispatcher::dispatch() for every CakeRequest object. Thus the default CakePHP dispatching process is
		   executed.
		*/
	}

}
