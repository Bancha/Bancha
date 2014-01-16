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

// To support PHP 5.4 strict we need to match the method signature exactly, therefore this workaround
// to support the old before 2.2 and the new 2.2+ signature.
if (substr(Configure::version(), 2, 3) < 2) {

/**
 * BanchaSingleDispatcher is a subclass of CakePHP's Dispatcher.
 *
 * See the method descriptions why this is required.
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
	class BanchaSingleDispatcher extends Dispatcher {
/**
 * Applies additionalParameters to the request to be dispatched. Unlike Dispatcher, BanchaSingleDispatcher does not
 * applies the routes.
 *
 * This function will be used for CakePHP 2.0.0 till 2.1.5
 *
 * @param CakeRequest $request CakeRequest object to mine for parameter information.
 * @param array $additionalParams An array of additional parameters to set to the request.
 *   Useful when Object::requestAction() is involved
 * @return CakeRequest The request object with routing params set.
 */
		public function parseParams(CakeRequest $request, $additionalParams = array()) {
			if (!empty($additionalParams)) {
				$request->addParams($additionalParams);
			}
			return $request;
		}
	}
} else {
	
/**
 * BanchaSingleDispatcher is a subclass of CakePHP's Dispatcher.
 *
 * See the method descriptions why this is required.
 * 
 * @package       Bancha.Lib.Bancha.Routing
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
	class BanchaSingleDispatcher extends Dispatcher {
/**
 * Applies additionalParameters to the request to be dispatched. Unlike Dispatcher, BanchaSingleDispatcher does not
 * applies the routes.
 *
 * This function will be used for CakePHP 2.2.0+
 *
 * @param CakeEvent $event containing the request, response and additional params
 * @return void
 */
		public function parseParams($event) {
			$request = $event->data['request'];
			Router::setRequestInfo($request);
			// this if clause is for backwards compatibility from 2.2.0 til 2.2.8, in 2.3.0 it got removed
			if ((substr(Configure::version(), 2, 3) == 2) && count(Router::$routes) == 0) {
				$namedExpressions = Router::getNamedExpressions();
				extract($namedExpressions);
				$this->_loadRoutes();
			}

			// dfault Dispatcher would not apply the routes, Bancha does not apply the routes
			//$params = Router::parse($request->url);
			//$request->addParams($params);

			if (!empty($event->data['additionalParams'])) {
				$request->addParams($event->data['additionalParams']);
			}
		}
	}
}
