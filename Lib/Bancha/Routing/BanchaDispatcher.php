<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
App::uses('ServerLogger', 'Bancha.Bancha/Logging');

/**
 * BanchaDispatcher
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
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
		CakeEventManager::instance()->attach(array($this, 'redirectHandler'), 'Controller.beforeRedirect');

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
					$this->logException($request, $e);
					ServerLogger::logIssue($this->getSignature($request), $e);
					$collection->addException($request['tid'], $e, $request);
				} // try catch
			} // if (!$skip_request)
		} // foreach

		// Combine the responses and return or output them.
		$responses = $collection->getResponses();
		if (isset($additionalParams['return']) && $additionalParams['return']) {
			return $responses->body();
		}

		// about every tenth usage send a small ping
		if(rand(1, 10)==1) {
			ServerLogger::logEnvironment();
		}

		$responses->send();
	}

	/**
	 * This handler will be called every time a redirect is triggered.
	 * Instead of doing a redirect this handler with throw an exception,
	 * createswhich will be catched by the BanchaDispatcher::dispatch
	 * and a ExtJS/Sencha Touch exception.
	 *
	 * @since  Bancha v 2.0.0
	 * @throws BanchaAuthLoginException If the user is not logged in and tried to access a denied method
	 * @throws BanchaAuthAccessRightsException If the user is not authorized to access this method
	 * @throws BanchaRedirectException If a redirect was triggered from app code
	 * @param  CakeEvent $event The event which triggered the redirect
	 * @return void
	 */
	public function redirectHandler($event) {
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
	}

	/**
	 * When a Controller throws a exception we will write it to the error log,
	 * since this is in normal cases a unwanted behavior. In most cases you
	 * want to return an array with success=>false to indicate to
	 * ExtJS/Sencha Touch that the request was not successfull.
	 *
	 * @since  Bancha v 2.0.0
	 * @param  CakeRequest $request   The request which caused the error
	 * @param  Exception   $exception The caugth exception
	 * @return void
	 */
	public function logException($request, $exception) {

		if(!Configure::read('Bancha.logExceptions') || Configure::read('debug')==2) {
			return; // don't log
		}

		// log the error
		$obj = new Object(); // just get an element to log the error
		$obj->log(
			'A Bancha request to '.$this->getSignature($request).' resulted in the following '.get_class($exception).':'.
			"\n".$exception."\n\n");
	}
	/**
	 * Build a string representation of the invocaton signature, used for error logs.
	 *
	 * @since  Bancha v 2.0.0
	 * @param  CakeRequest $request   The request
	 * @return void
	 */
	private function getSignature($request) {
		$signature = (!empty($request->params['plugin']) ? $request->params['plugin'].'.' : '').
						$request->params['controller'].'::'.$request->params['action'] . '(';
		foreach($request->params['pass'] as $pass) {
			$signature .= var_export($pass,true) . ', ';
		}
		if(!empty($request->params['pass'])) {
			// remove the trailing comma
			$signature = substr($signature, 0, strlen($signature)-2);
		}
		$signature =  $signature . ')';

		return $signature;
	}
}
