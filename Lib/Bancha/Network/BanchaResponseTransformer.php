<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Network
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

/**
 * BanchaResponseTransformer
 *
 * @package       Bancha
 * @subpackage    Network
 */
class BanchaResponseTransformer
{

	public static function transform($response, CakeRequest $request) {
		$data = array();
		$modelName = null;

		if ($request->controller)
		{
			$modelName = Inflector::camelize(Inflector::singularize($request->controller));
		}

		if ('index' == $request->action && $modelName)
		{
			foreach ($response as $i => $element) {
				$data[$i] = $element[$modelName];
			}
			$response = $data;
		}
		else if ('view' == $request->action && $modelName)
		{
			$response = array($response[$modelName]);
		}
		else if (in_array($request->action, array('add', 'edit')) && $modelName)
		{
			$response = $response[$modelName];
		}

		if (isset($request['extUpload']) && $request['extUpload'])
		{
			return '<html><body><textarea>' . json_encode($response) . '</textarea></body></html>';
		}

		return $response;
	}

}
