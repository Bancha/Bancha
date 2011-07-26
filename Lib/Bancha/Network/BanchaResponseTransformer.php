<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.Network
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

/**
 * BanchaResponseTransformer. Performs transformations on CakePHP responses in order to match Ext JS responses.
 *
 * @package       Bancha
 * @subpackage    Lib.Network
 */
class BanchaResponseTransformer {

/**
 * Performs various transformations on a request. This is required because CakePHP stores models in a different format
 * than expected from Ext JS.
 *
 * @param  array       $response A single response.
 * @param  CakeRequest $request  Request object.
 * @return array|string          Transformed response. If this is a response to an 'extUpload' request this is a string,
 *                               otherwise this is an array.
 */
	public static function transform($response, CakeRequest $request) {
		$data = array();
		$modelName = null;

		// Build the model name based on the name of the controller.
		if ($request->controller) {
			$modelName = Inflector::camelize(Inflector::singularize($request->controller));
		}

		if ('index' == $request->action && $modelName) {
			foreach ($response as $i => $element) {
				$data[$i] = $element[$modelName];
			}
			$response = $data;

		} else if ('view' == $request->action && $modelName) {
			$response = array($response[$modelName]);
		} else if (in_array($request->action, array('add', 'edit')) && $modelName) {
			$response = $response[$modelName];
		}

		// If this is an 'extUpload' request, we wrap the response in a valid HTML body.
		if (isset($request['extUpload']) && $request['extUpload']) {
			return '<html><body><textarea>' . json_encode($response) . '</textarea></body></html>';
		}

		return $response;
	}

}
