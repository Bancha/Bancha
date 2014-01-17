<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Network
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('CakeSenchaDataMapper', 'Bancha.Bancha');

// backwards compability with 5.2
if (function_exists('lcfirst') === false) {

/**
 * Make a string's first character lowercase.
 * 
 * @param string $str The string to transform
 * @return string     The transformed string
 */
	function lcfirst($str) {
		return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
	}

}

/**
 * BanchaResponseTransformer. Performs transformations on CakePHP responses in order to match Ext JS responses.
 *
 * @package       Bancha.Lib.Bancha.Network
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
class BanchaResponseTransformer {

/**
 * Performs various transformations on a request. This is required because CakePHP stores models in a different format
 * than expected from Ext JS.
 *
 * @param  array       $response    A single response.
 * @param  CakeRequest $CakeRequest Request object.
 * @return array|string             Transformed response. If this is a response to an 'extUpload' request this is a string,
 *                                  otherwise this is an array.
 * @throws BanchaException          If the response is null.
 */
	public static function transform($response, CakeRequest $CakeRequest) {
		$modelName = null;

		// Build the model name based on the name of the controller.
		if ($CakeRequest->controller) {
			$modelName = Inflector::camelize(Inflector::singularize($CakeRequest->controller));
		}

		if ($response === null) { // use the triple operator to not catch empty arrays
			throw new BanchaException("Please configure the {$modelName}Controllers {$CakeRequest->action} function to include a return statement as described in the Bancha documentation");
		}

		return BanchaResponseTransformer::transformDataStructureToSencha($response, $modelName);
	}

/**
 * Transform a CakePHP response to ExtJS/Sencha Touch structure,
 * otherwise just return the original response.
 * See also http://docs.banchaproject.org/resources/Supported-Controller-Method-Results.html
 *
 * @param  object $response  The input request from Bancha
 * @param  string $modelName The model name of the current request
 * @return array             ExtJS/Sencha Touch formated data
 */
	public static function transformDataStructureToSencha($response, $modelName) {
		// if we only got an array with a success property we expect
		// that this data is already in the correct format, so only
		// enforce that the success value is a boolean and we're done
		if (is_array($response) && isset($response['success'])) {

			// enforce that the success value is of type boolean
			$response['success'] = ($response['success'] === 'false') ? false : !!$response['success'];

			return $response; // everything done
		}

		// these are the cases where we transform data:

		// understand primitive responses
		if ($response === true || $response === false) {
			// this was an un-/successfull operation, return that to ext/touch
			return array(
				'success' => $response,
			);
		}

		// understand string and numeric responses
		if (is_string($response) || is_numeric($response)) {
			// this was an successfull operation with a string/number as data
			return array(
				'success' => true,
				'data' => $response,
			);
		}

		// this is a strange case, we got some class object, expect this should be the data
		if (!is_array($response)) {
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

		if ($modelName == 'Bancha') {
			// this is a request from the BanchaApi, nothing to transform here
			return $senchaResponse;
		}

		// transform model data
		$mapper = new CakeSenchaDataMapper($response, $modelName);

		if ($mapper->isSingleRecord()) {
			// filter the records
			$response = $mapper->walk(array('BanchaResponseTransformer', 'walkerDataTransformer'));
			$senchaModelName = lcfirst($modelName);

			// merge directly associated data into the primary model
			$primaryModel = $response[$senchaModelName];
			unset($response[$senchaModelName]);

			// this is standard cake single element structure
			$senchaResponse['data'] = array_merge($primaryModel, $response);

		} elseif ($mapper->isRecordSet()) {
			// this is standard cake multiple element structure

			// filter the records
			$response = $mapper->walk(array('BanchaResponseTransformer', 'walkerDataTransformer'));

			if ($mapper->isThreadedRecordSet()) {
				// if the response is threaded, the walker already transformed the data
				$senchaResponse['data'] = $response;
				return $senchaResponse;
			}

			// for normal responses, flatten the result here
			$conversionSuccessfull = true;
			$data = array();
			$senchaModelName = lcfirst($modelName);
			foreach ($response as $record) {
				if (!isset($record[$senchaModelName]) || !is_array($record[$senchaModelName])) {
					// there are entries which does not have data, strange
					$conversionSuccessfull = false;
					break;
				}

				// merge directly associated data into the primary model
				$primaryModel = $record[$senchaModelName];
				unset($record[$senchaModelName]);

				// add to result set
				array_push($data, array_merge($primaryModel, $record));
			}

			if ($conversionSuccessfull) {
				$senchaResponse['data'] = $data;
			} else {
				$senchaResponse['message'] = 'Expected the response to be multiple ' . $modelName . ' records, ' .
				'but some records were missing data, so did not convert data into ExtJS/Sencha Touch structure.';
			}

		} elseif ($mapper->isPaginatedSet()) {
			// this is a paging response

			// the records have a standard cake structure, so get them by using this function
			$data = BanchaResponseTransformer::transformDataStructureToSencha($response['records'], $modelName);
			// now add only the data to the response
			$senchaResponse['data'] = $data['data'];

			// Include the total number of records
			$senchaResponse['total'] = $response['count'];
		}

		return $senchaResponse;
	}

/**
 * This walker function is used by the transform function to re-format the output.
 * 
 * @param  string     $modelName the model name of the currently invoked model
 * @param  array|null $data      The data
 * @param  boolean    $isPrimary True if it is a primary model structure
 * @return array                 The result
 */
	public static function walkerDataTransformer($modelName, $data, $isPrimary) {
		// sencha expects model collections to have plural names
		$senchaModelName = $isPrimary ? $modelName : Inflector::pluralize($modelName);
		$senchaModelName = lcfirst($senchaModelName);

		// if the data is null, we just create an empty array
		if ($data === null) {
			return array($senchaModelName, array());
		}

		// get the model
		$Model = false;
		try {
			$Model = ClassRegistry::init($modelName, true);
		} catch(CakeException $e) {
			// there might be exceptions, in these cases do nothing
			return array(false, null);
		}

		if (!isset($Model->Behaviors->BanchaRemotable)) {
			// this model should not be exposed
			// (need to be tested here, since the filterRecord method might not be available)
			return array(false, null);
		}

		// filter data
		return array($senchaModelName, $Model->filterRecord($data));
	}

/**
 * Translates CakePHP CRUD to ExtJS CRUD method names.
 * 
 * @param string $request The CakePHP request
 * @return string         The Sencha Touch/Ext JS method name
 */
	public static function getMethod(CakeRequest $request) {
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
