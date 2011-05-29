<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Dispatcher', 'Routing');

/**
 * BanchaDispatcher
 *
 * @package bancha.libs
 */
class BanchaDispatcher
{

	/**
	 * Dispatches a BanchaRequest object.
	 *
	 * @param BanchaRequest $requests A BanchaRequest can contain multiple CakeRequest objects.
	 * @return boolean Success
	 */
	public function dispatch(BanchaRequest $requests)
	{
		// TODO: Actually implement BanchaDispatcher::dispatch()
		/* This is only some demo code. The idea of this method is that it receives a BanchaRequest object which
		   can contain multiple requests. The getRequests() method of BanchaRequest parses these multiple requests and
		   returns a CakeRequest object for every request. Therefore this method does only need to invoke the
		   Dispatcher (BanchaDispatcher::dispatch()) for every CakeRequest object. It is very import that the
		   $additionalParameters array does contain the 'return' value. Then Cakes default Dispatcher does return the
		   response instead of sending it to the client.
		*/
		$responses = array();
		foreach ($requests->getRequests() as $request)
		{
			// Call dispatcher for the given CakeRequest.
			$dispatcher = new Dispatcher();
			$responses[] = $dispatcher->dispatch($request, array('return' => true));
		}
		return $responses;
	}

}
