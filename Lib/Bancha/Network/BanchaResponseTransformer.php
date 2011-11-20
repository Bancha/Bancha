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
		$modelName = null;


		// Build the model name based on the name of the controller.
		if ($request->controller) {
			$modelName = Inflector::camelize(Inflector::singularize($request->controller));
		}
        
		if ($response == null) {
			throw new CakeException("Please configure the $modelName Controllers {$request->action} function to include a return statement as described in the bancha documentation");
		}
		
		$response = BanchaResponseTransformer::transformDataStructureToExt($modelName,$response);
		
		// If this is an 'extUpload' request, we wrap the response in a valid HTML body.
		if (isset($request['extUpload']) && $request['extUpload']) {
			return '<html><body><textarea>' . str_replace('"', '\"', json_encode($response)) . '</textarea></body></html>';
		}

		return $response;
	}
    
	/**
	 * Transform a cake response to extjs structure (associated models are not supportet!)
	 * otherwise just return the original response
	 *
	 * @param $modelName The model name of the current request
	 * @param $response The input request from Bancha
	 */
	public static function transformDataStructureToExt($modelName,$response) {
		if( isset($response[$modelName]) ) {
			// this is standard cake single element structure
			$response = array(
				'data' => $response[$modelName]
			);
		} else if( isset($response['0'][$modelName]) ) {
			// this is standard cake multiple element structure
			$data = array();
			foreach($response as $record) {
				array_push($data, $record[$modelName]);
			}
			$response = array('data' => $data);
		}
		
		return $response;
	}
	
	
	/**
	 * 
	 * translates CakePHP CRUD to ExtJS CRUD method names
	 * @param string $method
	 */
	public static function getMethod($method) {
		if('index' == $method) {
			return 'read';
		}
		if('edit' == $method) {
			return 'update';
		}
		if('add' == $method) {
			return 'create';
		}
		if('delete' == $method) {
			return 'destroy';
		}
		
		return $method;
	}

}
