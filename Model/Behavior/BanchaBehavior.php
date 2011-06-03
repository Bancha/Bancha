<?php
// TODO doku
class BanchaBehavior extends ModelBehavior {

	// TODO doku
	// alla array('create'=>true,...,'shareMetaData'=>true);
	private $actionIsAllowed;
	private $schema;
	private $model;

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

	/**
	 * Extracts all metadata which should be shared with the ExtJS frontend
	 *
	 * @param AppModel $model
	 * @return array all the metadata as array
	 */
	function extractBanchaMetaData() {
		// $this->schema = $Model->schema();
		// types: string, int, float, boolean, data
		
		// e.g. {name:'name', type:'string', defaultValue:'', persist:false} // persist is for generated values true
		// TODO primary wie setzen?, $model->$primaryKey contains the name of the primary key
		// ExtJS has a 'idPrimary' attribute which defaults to 'id' which IS the cakephp fieldname

		/**
		 * Stores the original schema from the model because it is protected
		 *
		 * @var Model.schema
		 */

		// $this->$schema = $model->schema();

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
		
		//$fields = $this->model->getColumnTypes();
		//$associations = $this->model->getAssociated();
		
		
		$fields = $this->getColumnTypes();
		$validations = $this->getValidations();
		$associations = $this->getAssociated();
		$sorters = $this->getSorters();

		$ExtMetaData = array (
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
	
		'Post', {
		    fields: ['id', 'user_id', 'title', 'body'],
		 
		    belongsTo: 'User',
		    hasMany: 'Comments'
		}
			 
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
		'User', {
			fields: [
		        {name: 'id', type: 'int'},
		        {name: 'name', type: 'string'}
		    ]
		}
			
	 */
	
	private function getColumnTypes() {
		$columns = $this->model->getColumnTypes();
		$cols = array();
		foreach ($columns as $field => $values) {
				array_push($cols, array( 'name' => $field, 'type' => $values));
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
			array_push($cols, array( 'type' => 'length', 'name' => $field, 'max' => $values['length']));
		}
		return $cols;
	}

	/**
	 * Returns an ExtJS formated array describing sortable fields
	 *
	 * @return array ExtJS formated  { property: 'name', direction: 'ASC'	}
	 */

	private function getSorters() {
		// TODO which kind of arrays/strings does CakePHP return?
		$sorters = array();
		if ( is_array($this->model->order) ) {			
			// var $order = array("Model.field" => "asc", "Model.field2" => "DESC");
			foreach($this->model->order as $key => $value) {
				array_push($sorters, array( 'property' => strtok($key, '.'), 'direction' => $value));
			}
		} else {
			/* all possible ways to express this property
			 1. var $order = "field"
			 2. var $order = "Model.field";
			 3. var $order = "Model.field asc";
			 4. var $order = "Model.field ASC";
			 5. var $order = "Model.field DESC";
			 */
		}
		// $sorters = { property: 'name', direction: 'ASC' };
		return $sorters;
	}
}
?>