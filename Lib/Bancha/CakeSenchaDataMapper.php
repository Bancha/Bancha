<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Lib
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.3
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * CakeSenchaDataMapper
 * A Helper class for building the ExtJS/Sencha Touch data structures from 
 * CakePHP ones.
 *
 * @package       Bancha
 * @subpackage    Lib
 */
class CakeSenchaDataMapper {
	private $data;
	private $primary;

	/**
	 * Build a new mapper.
	 * @param array  $data             The CakePHP data in any format (single, multiple)
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
}

