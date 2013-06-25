<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Lib.Routing
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Dispatcher', 'Routing');
App::uses('BanchaResponseCollection', 'Bancha.Bancha/Network');
App::uses('BanchaSingleDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaAuthLoginException', 'Bancha.Bancha/Exception');
App::uses('BanchaAuthAccessRightsException', 'Bancha.Bancha/Exception');
App::uses('BanchaRedirectException', 'Bancha.Bancha/Exception');

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

		// CakePHP should think that every Bancha request is a POST request.
		$_SERVER['REQUEST_METHOD'] = 'POST';
		
		// setup a handler for redirects
		CakeEventManager::instance()->attach(function($event) {
			$controller = $event->subject();
			list($url, $status, $exit) = $event->data;

			// Handle actions fron AuthComponent
			if(isset($controller->Auth) && !$controller->Auth->loggedIn()) {
				throw new BanchaAuthLoginException('Please login first. Maybe your session expired.');
			}
			if(isset($controller->Auth) && !$controller->Auth->isAuthorized($controller->Auth->user())) {
				throw new BanchaAuthAccessRightsException('You are not allowed to see this page.');
			}

			// general redirect handling, will trigger an exception
			throw new BanchaRedirectException($event->subject()->name . 'Controller forced a redirect to ' . $url . (empty($status) ? '' : ' with status '.$status));
		}, 'Controller.beforeRedirect');
		
		// Iterate through all requests, dispatch them and add the response to the transformer object.
		foreach ($requests->getRequests() as $request) {
			$skip_request = false;

			if (!$skip_request) {
				// Call dispatcher for the given CakeRequest.
				// We need to use a sub classes disaptcher, because some parameters are missing in Bancha requests and
				// because we need to full response, not only the body of the response.
				$dispatcher = new BanchaSingleDispatcher();

				try {
					// dispatch the request
					$response = new CakeResponse(array('charset' => Configure::read('App.encoding')));
					$dispatcher->dispatch($request, $response, array('return' => true));
					
					// add result to response colection
					$collection->addResponse(
						$request['tid'],
						$response,
						$request
					);
				} catch (Exception $e) {
					$collection->addException($request['tid'], $e, $request);
				} // try catch
			} // if (!$skip_request)
		} // foreach

		// Combine the responses and return or output them.
		$responses = $collection->getResponses();
		if (isset($additionalParams['return']) && $additionalParams['return']) {
			return $responses->body();
		}
		$responses->send();
	}

}
