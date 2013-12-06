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
if(substr(Configure::version(), 2, 3) < 2) {
	/**
	 * See BanchaSingleDispatcher class.
	 *
	 * @package       Bancha.Lib.Bancha.Routing
	 * @since         Bancha v 2.0.0
	 * @author        Roland Schuetz <mail@rolandschuetz.at>
	 */
	class BanchaSingleDispatcher_Parent extends Dispatcher {
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
	 * See BanchaSingleDispatcher class.
	 *
	 * @package       Bancha.Lib.Bancha.Routing
	 * @since         Bancha v 2.0.0
	 * @author        Roland Schuetz <mail@rolandschuetz.at>
	 */
	class BanchaSingleDispatcher_Parent extends Dispatcher {
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

/**
 * BanchaSingleDispatcher is a subclass of CakePHP's Dispatcher.
 *
 * See the method descriptions why this is required.
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */
class BanchaSingleDispatcher extends BanchaSingleDispatcher_Parent {

/**
* Initializes the components and models a controller will be using.
* Triggers the controller action, and invokes the rendering if Controller::$autoRender is true and echo's the output.
* Otherwise the return value of the controller action are returned.
*
* Works like {@see Dispatcher::_invoke()} but returns the full response instead the body only.
*
* Bancha needs to overwrite this method because we need the full response object not only the body of the response
* object on return.
*
* @param Controller $controller Controller to invoke
* @param CakeRequest $request The request object to invoke the controller for.
* @param CakeResponse $response The response object to receive the output
* @return CakeResponse te resulting response object
 */
	protected function _invoke(Controller $controller, CakeRequest $request, CakeResponse $response) {
		$controller->constructClasses();
		$controller->startupProcess();

		$render = true;
		$result = $controller->invokeAction($request);
		if ($result instanceof CakeResponse) {
			$render = false;
			$response = $result;
		}

		if ($render && $controller->autoRender) {
			$response = $controller->render();
		} elseif (!($result instanceof CakeResponse) &&
			$response->body() === null
		) {
			$response->body($result);
		}
		$controller->shutdownProcess();

		if (isset($request->params['return'])) {
			return $response; // <-------------- only this line is changed, original: return $response->body();
		}
		$response->send();
	}
}
