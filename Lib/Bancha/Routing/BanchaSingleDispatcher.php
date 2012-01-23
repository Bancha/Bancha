<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.Routing
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Dispatcher', 'Routing');

/**
 * BanchaSingleDispatcher is a subclass of CakePHP's Dispatcher.
 *
 * See the method descriptions why this is required.
 *
 * @package    Bancha
 * @subpackage Lib.Routing
 */
class BanchaSingleDispatcher extends Dispatcher {

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
* @return void
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
		} elseif ($response->body() === null) {
			$response->body($result);
		}
		$controller->shutdownProcess();

		if (isset($request->params['return'])) {
			return $response; // <-------------- only this line is changed, original: return $response->body();
		}
		$response->send();
	}

	/**
	 * Applies additionalParameters to the request to be dispatched. Unlike Dispatcher, BanchaSingleDispatcher does not
	 * applies the routes.
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
