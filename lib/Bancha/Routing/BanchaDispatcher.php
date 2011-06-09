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
class BanchaDispatcher {

/**
 * Dispatches a BanchaRequest object. It uses the standard CakePHP dispatcher to dispatch the single CakeRequest
 * objects returned by BanchaRequest.
 *
 * @param BanchaRequest $requests A BanchaRequest can contain multiple CakeRequest objects.
 * @return array Array where every element is the response of a single request.
 */
	public function dispatch(BanchaRequest $requests) {
		// TODO: Parameter to define if the response should be returned or echoed.
		$response = new BanchaResponse();
		foreach ($requests->getRequests() as $request) {
			// Call dispatcher for the given CakeRequest.
			$dispatcher = new BanchaSingleDispatcher();
			$response->addResponse($dispatcher->dispatch($request, array('return' => true)));
		}
		return $response;
	}

}
