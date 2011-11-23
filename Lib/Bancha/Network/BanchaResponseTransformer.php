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
        
		if ($response === null) { // use the triple operator to not catch empty arrays
			throw new CakeException("Please configure the {$modelName}Controllers {$request->action} function to include a return statement as described in the Bancha documentation");
		}
		
		return BanchaResponseTransformer::transformDataStructureToExt($modelName,$response);
	}
    
	/**
	 * Transform a cake response to extjs structure (associated models are not supportet!)
	 * otherwise just return the original response.
	 * See also https://github.com/Bancha/Bancha/wiki/Supported-Controller-Method-Results
	 *
	 * @param $modelName The model name of the current request
	 * @param $response The input request from Bancha
	 * @param $controller The used controller
	 * @return extjs formated data array
	 */
	public static function transformDataStructureToExt($modelName,$response) {
		
		if($response===false) {
			// this was an unsuccessfull operation, return that to ext
			return array(
				'success' => false,
			);
		}
		
		// expect a successfull operation, but check
		$sucess = isset($response['success']) ? !!$response['success'] : true;
		
		if( isset($response[$modelName]) ) {
			// this is standard cake single element structure
			$response = array(
				'success' => $sucess,
				'data' => $response[$modelName]
			);
			
		} else if( isset($response['0'][$modelName]) ) {
			// this is standard cake multiple element structure
			$data = array();
			foreach($response as $record) {
				array_push($data, $record[$modelName]);
			}
			$response = array(
				'success' => $sucess,
				'data' => $data
			);
			
		} else if( isset($response['records']['0'][$modelName]) ) {
			// this is a paging response
			
			// the records have standard cake structure, so get them
			$data = BanchaResponseTransformer::transformDataStructureToExt($modelName,$response['records']);

			// create response including the total number of records
			$response = array(
				'success' => $sucess,
				'data'  => $data['data'],
				'total' => $response['count']
			);
		}
		
		return $response;
	}
	
	
	/**
	 * 
	 * translates CakePHP CRUD to ExtJS CRUD method names
	 * @param string $method
	 */
	public static function getMethod($request) {
		switch($request->action) {
			case 'index': // fall through, it's the same as view
			case 'view':
				return ($request['isFormRequest']) ? 'load' : 'read';
			case 'edit':
				return ($request['isFormRequest']) ? 'submit' : 'update';
			case 'add':
				return ($request['isFormRequest']) ? 'submit' : 'create';
			case 'delete':
				return 'destroy';
			default:
				return $request->action;
		}
	}

}
