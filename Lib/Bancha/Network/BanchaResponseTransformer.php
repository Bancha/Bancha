<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Lib.Network
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
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
			throw new BanchaException("Please configure the {$modelName}Controllers {$request->action} function to include a return statement as described in the Bancha documentation");
		}
		
		return BanchaResponseTransformer::transformDataStructureToExt($modelName,$response);
	}
    
	/**
	 * Transform a cake response to extjs structure (associated models are not supported!)
	 * otherwise just return the original response.
	 * See also http://docs.banchaproject.org/resources/Supported-Controller-Method-Results.html
	 *
	 * @param $modelName The model name of the current request
	 * @param $response The input request from Bancha
	 * @param $controller The used controller
	 * @return extjs formated data array
	 */
	public static function transformDataStructureToExt($modelName, $response) {
		
		// if we only got an array with a asuccess proeprty we expect 
		// that this data is already in the correct format, so only 
		// enfore that the success value is a boolean and we're done
		if(is_array($response) && isset($response['success'])) {
			
			// enforce that the success value is of type boolean
			$response['success'] = $response['success']==='false' ? false : !!$response['success'];

			return $response; // everything done
		}

		// these are the cases where we transform data:

		// understand primitive responses
		if($response===true || $response===false) {
			// this was an un-/successfull operation, return that to ext
			return array(
				'success' => $response,
			);
		}

		// understand string and numeric responses
		if(is_string($response) || is_numeric($response)) {
			// this was an successfull operation with a string/number as data
			return array(
				'success' => true,
				'data' => $response,
			);
		}

		// this is a strange case, we got some class object, expect this should be the data
		if(!is_array($response)) {
			return array(
				'success' => true,
				'data' => $response,
			);
		}
		
		// we got some data array here, wrap it in the sencha response
		// and try to transform it
		$senchaResponse = array(
			'success' => true,
			'data' => $response
		);

		
		if( isset($response[$modelName]) ) {
			// this is standard cake single element structure
			$senchaResponse['data'] = $response[$modelName];
			
		} else if( isset($response['0']) && isset($response['0'][$modelName]) && 
					is_array($response['0'][$modelName])) {
			// this is standard cake multiple element structure

			$conversionSuccessfull = true;
			$data = array();
			foreach($response as $record) {
				if(!isset($record[$modelName]) || !is_array($record[$modelName])) {
					// there are entries which does not have data, strange
					$conversionSuccessfull = false;
					break;
				}
				array_push($data, $record[$modelName]);
			}

			if($conversionSuccessfull) {
				$senchaResponse['data'] = $data;
			} else {
				$senchaResponse['message'] = 'Expected the response to be multiple ' . $modelName . ' records, '.
				'but some records were missing data, so did not convert data into ExtJS/Sencha Touch structure.';
			}
			
		} else if( isset($response['records']) && isset($response['count']) && 
				(isset($response['records']['0'][$modelName]) || 						// paginagted records with records
				(is_array($response['records']) && $response['count']==0))) {   		// pagination with zero records
			// this is a paging response

			// the records have standard cake structure, so get them by using this function
			$data = BanchaResponseTransformer::transformDataStructureToExt($modelName, $response['records']);
			// now add only the data to the response
			$senchaResponse['data'] = $data['data'];

			// Include the total number of records
			$senchaResponse['total'] = $response['count'];
		}

		return $senchaResponse;
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
				return 'read';
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
