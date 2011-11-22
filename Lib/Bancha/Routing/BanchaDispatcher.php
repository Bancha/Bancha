<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.Routing
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Dispatcher', 'Routing');
App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');
App::uses('BanchaSingleDispatcher', 'Bancha.Bancha/Routing');

/**
 * BanchaDispatcher
 *
 * @package    Bancha
 * @subpackage Lib.Routing
 */
class BanchaDispatcher {

/**
 * Dispatches a BanchaRequestCollection object. It uses the standard CakePHP dispatcher to dispatch the single
 * CakeRequest objects returned by BanchaRequest. Further it uses BanchaResponseCollection to transform the responses
 * into a single CakeResponse object. If the 'return' option in the $additionalParams argument is TRUE, the body of the
 * response is returned instead of directly sent to the browser.
 *
 * @param BanchaRequestCollection $requests A BanchaRequestCollection can contain multiple CakeRequest objects.
 * @param array $additionalParams If 'return' is TRUE, the body is returned instead of sent to the browser.
 * @return string|void If 'return' is TRUE, the body is returned otherwise void is returned.
 */
	public function dispatch(BanchaRequestCollection $requests, $additionalParams = array()) {
		$collection = new BanchaResponseCollection();

		// Iterate through all requests, dispatch them and add the response to the transformer object.
		foreach ($requests->getRequests() as $request) {
			// Ensure consitency if Client ID is given.
			$ensure_consitency = false;
			$skip_request = false;
			if ($request['client_id']) {
				$ensure_consitency = true;
			}
			if ($ensure_consitency) {
				$current_tid = null;
				$client_file = TMP . 'bancha-clients/' . $request['client_id'] . '.txt';
				if (file_exists($client_file)) {
					$current_tid = trim(file_get_contents($client_file));
				}

				if ($current_tid && $request['tid'] >= $current_tid) {
					$skip_request = true;
				}

				$current_tid = $request['tid'];
				file_put_contents($client_file, $current_tid);
			}

			if (!$skip_request) {
				// Call dispatcher for the given CakeRequest.
				// We need to use a sub classes disaptcher, because some parameters are missing in Bancha requests and
				// because we need to full response, not only the body of the response.
				$dispatcher = new BanchaSingleDispatcher();
				try {
					$collection->addResponse(
						$request['tid'],
						$dispatcher->dispatch($request, array('return' => true)),
						$request
					);
				} catch (Exception $e) {
					$collection->addException($request['tid'], $e, $request);
				} // try catch
			} // if (!$skip_request)

			if ($ensure_consitency) {
				// Remove current TID from client file in order to allow execution of next request from this client.
				file_put_contents($client_file, '');
			}
		} // foreach

		// Combine the responses and return or output them.
		$responses = $collection->getResponses();
		if (isset($additionalParams['return']) && $additionalParams['return']) {
			return $responses->body();
		}
		$responses->send();
	}

}
