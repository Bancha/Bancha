<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * CakeSenchaDataMapper
 * A Helper class for building the ExtJS/Sencha Touch data structures from
 * CakePHP ones.
 *
 * @package       Bancha.Lib.Bancha
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.0.0
 */
class CakeSenchaDataMapper {
	private $data;
	private $primary;

	/**
	 * Build a new mapper.
	 * @param array  $data             The CakePHP data in any format (single, multiple, threaded, paginated)
	 * @param string $primaryModelName The name of the primary cake model for this dataset
	 */
	function __construct($data, $primaryModelName) {
		$this->data = $data;
		$this->primary = $primaryModelName;
	}

	/**
	 * Returns true if the current set is in cakes find('first') data structure
	 * @return boolean True if this is a single record array
	 */
	public function isSingleRecord() {
		return isset($this->data[$this->primary]);
	}

	/**
	 * Returns true if the current set is in cakes find('all') data structure
	 * @return boolean True if this is a record array for possibly multiple records
	 */
	public function isRecordSet() {
		return isset($this->data['0']) && isset($this->data['0'][$this->primary]) && is_array($this->data['0'][$this->primary]);
	}

	/**
	 * Returns true if the current set is in cakes find('threaded') data structure
	 * @return boolean True if this is a record array is threaded
	 */
	public function isThreadedRecordSet() {
		return $this->isRecordSet() && isset($this->data[0]['children']);
	}

	/**
	 * Returns true if the current set is in Bancha's pagination data structure
	 *
	 * Example:
	 *     array(
	 *         'count' => 100,
	 *         'records' => array( ... find('all') structure... )
	 *     )
	 *
	 * @return boolean True if this is a pagination set
	 */
	public function isPaginatedSet() {
		return isset($this->data['records']) && isset($this->data['count']) &&  // this is how a paginated result set should look with Bancha
				(isset($this->data['records']['0'][$this->primary]) || 			// paginagted records with records
				(is_array($this->data['records']) && $this->data['count']==0)); // pagination with zero records
	}

	/**
	 * This function walks through the input $data and calls the given callback 
	 * for each record found. The callback has to have two parameters:
	 * 
	 *  - string $modelName: The name of the model (e.g. 'User')
	 *  - array  $data: The model data, not nested data (e.g. array('id', 'name')). 
	 *                  If there is a hasMany or hasManyAndBelongsToMany association 
	 *                  and there is no record, the callable function will be called 
	 *                  once with the model name (to may transform) and null as $data.
	 *                  Note that the record may have nested records inside the data,
	 *                  deleting or modifing those results in the walker not 
	 *                  visiting them.
	 *
	 * The callable should return a numeric array, first param is the new record
	 * key, second the data. Setting the record key to false results in removeing
	 * the entry.
	 *
	 * The walker currently does not support threaded records.
	 * 
	 * The walker has a depth first approach for walking the data. It has no 
	 * side effects.
	 * 
	 * @param callback $callable PHP valid callback to be called for every 
	 *                           record data found in the input data.
	 * @return array             The resulting data array.
	 */
	public function walk($callable) {
		if($this->isPaginatedSet()) {
			// walk though the record entries only
			$data = $this->data;
			$data['records'] = $this->_walk($callable, $data['records']);
			return $data;
		} else {
			// walk though the records
			return $this->_walk($callable, $this->data);
		}
	}

	private function _walk($callable, $data) {

		// find all data entries
		foreach($data as $key => $value) {
			if(!is_array($value)) {
				continue; // this is simply a record field
			}
			if(empty($value)) {
				// this is an empty array of nested data,
				// transform the model name.
				list($newKey, $newData) = call_user_func($callable, $key, null, true);
				unset($data[$key]);
				if($newKey!==false) $data[$newKey] = array();
				continue; 
			}

			if(isset($value[0])) {
				// this is a multi-record result of non-primary records, 
				// walk each entry
				$data = $this->_walkNestedSet($callable, $data, $key);
				continue;
			}

			if(is_numeric($key)) {
				// we are currently in a set of records, these are primary models
				$data[$key] = $this->_walk($callable, $value);
				continue;
			}


			// found new record
			// key is the model name, value is the data
			list($newKey, $newData) = call_user_func($callable, $key, $value, true);
			unset($data[$key]);

			// now walk the child data
			if($newKey!==false) $data[$newKey] = $this->_walk($callable, $newData);
		}

		return $data;
	}

	private function _walkNestedSet($callable, $data, $modelName) {

		// remove original entry
		$nestedData = $data[$modelName];
		unset($data[$modelName]);

		// walk through all entries
		$newKey = '';
		$newNestedData = array(); // we need a new array to not break the foreach when removing an entry
		foreach($nestedData as $key => $value) {
			// key is the index, value is the data
			list($newKey, $newData) = call_user_func($callable, $modelName, $value, false);

			// now walk in nested record
			if($newKey!==false) array_push($newNestedData, $this->_walk($callable, $newData));
		}
		$data[$newKey] = $newNestedData;

		return $data;
	}
}

