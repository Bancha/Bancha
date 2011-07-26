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

/**
 * BanchaSingleDispatcher
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
 * @param Controller $controller Controller to invoke
 * @param CakeRequest $request The request object to invoke the controller for.
 * @return string CakeResponse object or void.
 * @throws MissingActionException when the action being called is missing.
 */
	protected function _invoke(Controller $controller, CakeRequest $request) {
		$controller->constructClasses();
		$controller->startupProcess();

		$methods = array_flip($controller->methods);

		if (!isset($methods[$request->params['action']])) {
			if ($controller->scaffold !== false) {
				return new Scaffold($controller, $request);
			}
			throw new MissingActionException(array(
				'controller' => Inflector::camelize($request->params['controller']) . "Controller",
				'action' => $request->params['action']
			));
		}
		$result = call_user_func_array(array(&$controller, $request->params['action']), $request->params['pass']);
		$response = $controller->getResponse();

		if ($controller->autoRender) {
			$controller->render();
		} elseif ($response->body() === null) {
			$response->body($result);
		}
		$controller->shutdownProcess();
		if (isset($request->params['action'])) {
			$action = $request->params['action'];
		}
		if (isset($request->params['method'])) {
			
		}
		if (isset($request->params['return'])) {
			return $response;
		}
		$response->send();
	}

	/**
	 * Applies additionalParameters to the request to be dispatched. Unlike Dispatcher, BanchaSingleDispatcher does not
	 * applies the routeas.
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
