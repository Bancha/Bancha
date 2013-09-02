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
	 * @param BanchaRequestCollection $requests A BanchaRequestCollection can contains multiple CakeRequest objects.
	 * @param CakeResponse            $response The CakePHP response object to send the content or return the body.
	 * @param array                   $additionalParams If 'return' is TRUE, the body is returned instead of sent to the browser.
	 * @return string|void            If 'return' is TRUE, the body is returned otherwise void is returned.
	 */
	public function dispatch(BanchaRequestCollection $requests, CakeResponse $response = null, $additionalParams = array()) {
		if($response === null) {
			// Legacy support for Bancha 1.x
			$response = new CakeResponse();
			if(Configure::read('debug') == 2) {
				echo 'Bancha Error: Please update your webroot/bancha-dispatcher.php file the Bancha 2 version!';
			}
		}
		$collection = new BanchaResponseCollection($response);

		//<bancha-basic>
		/**
		 * Yes, if you want to hack this software, it is pretty simply. We want
		 * to spend our time making Bancha even better, not adding piracy
		 * protection.
		 *
		 * Please consider buying a license if you like Bancha, we are a
		 * small company and if we don't earn money to life from this project
		 * we are not able to further develop Bancha.
		 */
		//</bancha-basic>
		/*<bancha-basic>
		if(Configure::read('Bancha.isPro') != false) {
			echo 'Bancha Error: You are using Bancha Basic, please don\'t change the Bancha.isPro config!';
		}
		</bancha-basic>*/

		$allowedDomains = Configure::read('Bancha.allowedDomains');
		if ($allowedDomains && $allowedDomains!=='*' && !isset($_SERVER['HTTP_ORIGIN'])) {
			// we need to have a origin to validate the domain!
			if(Configure::read('debug') == 2) {
				echo 'Bancha Error: Bancha expects that any request has a '.
					 'HTTP_ORIGIN header.';
			}
			return; // abort
		}

		if ($allowedDomains && $allowedDomains!=='*' &&
			!in_array($_SERVER['HTTP_ORIGIN'], $allowedDomains)) {
			// this domain is prohibited according to the access control list
			// block it
			if(Configure::read('debug') == 2) {
				echo 'Bancha Error: According to the '.
					 'Configure::read("Bancha.allowedDomains") '.
					 'this request is not allowed!';
			}
			return; // abort
		}

		// If Bancha is used from a different domain, the browser will send a "preflight"
		// request (request type OPTIONS) before sending the actual POST request.
		// Simply set the correct COR headers and exit
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

			// return only the headers and not the content
			$this->send($response);
			return;
		}


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
					$subResponse = new CakeResponse(array('charset' => Configure::read('App.encoding')));
					$dispatcher->dispatch($request, $subResponse, array('return' => true));

					// add result to response colection
					$collection->addResponse(
						$request['tid'],
						$subResponse,
						$request
					);
				} catch (Exception $e) {
					$this->logException($request, $e);
					ServerLogger::logIssue($this->getSignature($request), $e);
					$collection->addException($request['tid'], $e, $request);
				} // try catch
			} // if (!$skip_request)
		} // foreach

		// Combine the responses
		$collection->getResponses();

		// about every tenth usage send a small ping
		if(rand(1, 10)==1) {
			ServerLogger::logEnvironment();
		}

		// Return or send response
		if (isset($additionalParams['return']) && $additionalParams['return']) {
			return $response->body();
		}

		return $this->send($response);
	}

	/**
	 * Set the appropriate CORS headers, if the *Bancha.allowedDomains* config
	 * is set. Then send the response.
	 *
	 * @param  CakeResponse $response The CakeResponse to send
	 * @return void
	 */
	private function send($response) {

		// Bancha might be available from multiple locations
		// See in bootstrap.php for the Bancha.allowedDomains config
		if(Configure::read('Bancha.allowedDomains') !== false) {
			// configure the access controll headers
			$response->header(array(
				'Access-Control-Allow-Methods' => 'POST, OPTIONS',
				'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type',
													// we are only able to set one domain, see https://cakephp.lighthouseapp.com/projects/42648-cakephp/tickets/3960
				'Access-Control-Allow-Origin'  => (Configure::read('Bancha.allowedDomains')=='*' ? '*' : $_SERVER['HTTP_ORIGIN']),
				'Access-Control-Max-Age'       => '3600' // require preflight request only once
			));
		}

		$response->send();
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
