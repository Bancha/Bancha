<?php

App::uses('ModelBehavior', 'Model');

// TODO doku
class BanchaBehavior extends ModelBehavior {

	// TODO doku
	// alla array('create'=>true,...,'shareMetaData'=>true);
	private $actionIsAllowed;
	private $schema;
	private $model;
	
	private $types = array( 
		"integer" => "int",
		"string" => "string",
		"datetime" => "date",
		"float" => "float",
		"text" => "text",
		"boolean" => "boolean"
		);

/**
 *  TODO doku
 *
 * @param object $Model instance of model
 * @param array $config array of configuration settings.
 * @return void
 * @access public
 */
	function setup(&$Model, $config = array()) {
		if(is_string($config)) {
			// TODO in array form umwandeln
		}
		$this->model = $Model;
		$this->schema = $Model->schema();
		$this->actionIsAllowed = $config;
	}
	
	/** set the model explicit as cakephp does not instantiate the behavior for each model
	 * 
	 */
	
	function setBehaviorModel(&$Model) {
		$this->model = $Model;
		$this->schema = $Model->schema();
	}

/**
 * Extracts all metadata which should be shared with the ExtJS frontend
 *
 * @param AppModel $model
 * @return array all the metadata as array
 */
	function extractBanchaMetaData() {
		
		//TODO persist: persist is for generated values true
		// TODO primary wie setzen?, $model->$primaryKey contains the name of the primary key
		// ExtJS has a 'idPrimary' attribute which defaults to 'id' which IS the cakephp fieldname

		$ExtMetaData = array();

		// TODO check types (CakePHP vs ExtJS) and convert if necessary 
		
		/* cakePHP types 	MySQL types						ExtJS Types
		 * 	primary_key 	NOT NULL auto_increment			???
		 *	string 			varchar(255)
		 *	text 			text
		 *	integer 		int(11)
		 *	float 			float
		 *	datetime 		datetime
		 *	timestamp 		datetime
		 *	time 			time
		 *	date 			date
		 *	binary 			blob
		 *	boolean 		tinyint(1)
		 */
		
		
		$fields = $this->getColumnTypes();
		$validations = $this->getValidations();
		$associations = $this->getAssociated();
		$sorters = $this->getSorters();

		$ExtMetaData = array (
			'idProperty' => 'id',
			'fields' => $fields, 
			'validations' => $validations, 
			'associations' => $associations,
			'sorters' => $sorters
		);

		return $ExtMetaData;
	}
	
/**
 * Return the Associations as ExtJS-Assoc Model
 * should look like this: 
 *
 * 'Post', {
 *     fields: ['id', 'user_id', 'title', 'body'],
 *	   belongsTo: 'User',
 *	   hasMany: 'Comments'
 *	}
 */
	private function getAssociated() {
		$assocs = $this->model->getAssociated();
		$return = array();
		foreach ($assocs as $field => $value) {
			array_push($return, array ($value => $field));
		}
		return $return;
	}
	
/**
 * return the model columns as ExtJS Fields
 * 
 * should look like 
 * 
 * 'User', {
 *   fields: [
 *     {name: 'id', type: 'int'},
 *     {name: 'name', type: 'string'}
 *   ]
 * }
 */
	private function getColumnTypes() {
		$columns = $this->model->getColumnTypes();
		$cols = array();
		foreach ($columns as $field => $values) {
				array_push($cols, array( 'name' => $field, 'type' => $this->types[$values]));
		}
		return $cols;
	}

/**
 * Returns an ExtJS formated array of field names, validation types and constraints.
 * atm only the max length constraint is retrived
 *
 * @return array ExtJS formated {type, name, max}
 */
	private function getValidations() {
		$columns = $this->schema;
		if (empty($columns)) {
			trigger_error(__d('cake_dev', '(Model::getColumnTypes) Unable to build model field data. If you are using a model without a database table, try implementing schema()'), E_USER_WARNING);
		}
		$cols = array();
		foreach ($columns as $field => $values) {
			if(isset($values['length'])) {
				array_push($cols, array( 'type' => 'length', 'name' => $field, 'max' => $values['length']));
			}
		}
		return $cols;
	}

/**
 * Returns an ExtJS formated array describing sortable fields
 * this is '$order' in cakephp
 *
 * @return array ExtJS formated  { property: 'name', direction: 'ASC'	}
 */
	private function getSorters() {
		// TODO TechDocu: only arrays are allowed as $order
		$sorters = array();
		if ( is_array($this->model->order) ) {			
			foreach($this->model->order as $key => $value) {
				$token = strtok($key, ".");
				$key = strtok(".");
				array_push($sorters, array( 'property' => $key, 'direction' => $value));
			}
		} else {
			//debug("model->order is not an array");
		}
		return $sorters;
	}

}
