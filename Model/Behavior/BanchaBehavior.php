<?php
/**
 * BanchaBahavior file.
 *
 * @package    Bancha
 * @subpackage Model.Behavior
 */

App::uses('ModelBehavior', 'Model');

/**
 * BanchaBahavior.
 *
 * @package    Bancha
 * @subpackage Model.Behavior
 */
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
		"date" => "date",
		"float" => "float",
		"text" => "text",
		"boolean" => "boolean"
		);

		//  formater for the validation rules
		// TODO comply with CakePHP validation rules
//		private $formater = array(
//		'alpha' => '/^[a-zA-Z_]+$/',
//		'alphanum' => '/^[a-zA-Z0-9_]+$/',
//		'email' => '/^(\w+)([\-+.][\w]+)*@(\w[\-\<wbr>w]*\.){1,5}([A-Za-z]){2,6}$/',
//		'url' => '/(((^https?)|(^ftp)):\/\/([\-\<wbr>w]+\.)+\w{2,3}(\/[%\-\w]+(\.\<wbr>w{2,})?)*(([\w\-\.\?\\\/+@&amp;#;`<wbr>~=%!]*)(\.\w{2,})?)*\/?)/i)',
//		);
		
	private $formater = array(
		'alpha' => 'banchaAlpha',
		'alphanum' => 'banchaAlphanum',
		'email' => 'banchaEmail',
		'url' => 'banchaUrl',
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
         * Custom validation rule for uploaded files.
         *
         *  @param Array $data CakePHP File info.
         *  @param Boolean $required Is this field required?
         *  @return Boolean
        */
        function validateFile($data, $required = false) {
                // Remove first level of Array ($data['Artwork']['size'] becomes $data['size'])
                $upload_info = array_shift($data);

                // No file uploaded.
                if ($required && $upload_info[’size’] == 0) {
                        return false;
                }

                // Check for Basic PHP file errors.
                if ($upload_info[‘error’] !== 0) {
                        return false;
                }

                // Finally, use PHP’s own file validation method.
                return is_uploaded_file($upload_info[‘tmp_name’]);
        }
        
        // TODO remove workarround for 'file' validation
        function file($check) {
        	return true;
        }

/**
 * Return the Associations as ExtJS-Assoc Model
 * should look like this:
 * <code>
 * associations: [
 *        {type: 'hasMany', model: 'Post',    name: 'posts'},
 *        {type: 'hasMany', model: 'Comment', name: 'comments'}
 *   ]
 * </code>
 *   
 *   (source http://docs.sencha.com/ext-js/4-0/#/api/Ext.data.Model)
 *   
 *   in cakephp it is stored as this <code>Array ( [Article] => hasMany )</code>
 */
	private function getAssociated() {
		$assocs = $this->model->getAssociated();
		$return = array();
		foreach ($assocs as $field => $value) {
			$name = lcfirst(Inflector::pluralize($field)); //generate a handy name
			$return[] = array ('type' => $value, 'model' => $field, 'name' => $name);
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
		$columns = $this->model->validate;
		if (empty($columns)) {
			//some testcases fail with this
			//trigger_error(__d('cake_dev', '(Model::getColumnTypes) Unable to build model field data. If you are using a model without a database table, try implementing schema()'), E_USER_WARNING);
		}
		$cols = array();
		foreach ($columns as $field => $values) {
			if(isset($values['notempty'])) {
				$cols[] = array(
					'type' => 'presence',
					'name' => $field,
				);
			}

			if(isset($values['minLength'])) {
				$cols[] = array(
					'type' => 'length',
					'name' => $field,
					'min' => $values['minLength']['rule'][1],
				);
			}

			if(isset($values['maxLength'])) {
				$cols[] = array(
					'type' => 'length',
					'name' => $field,
					'max' => $values['maxLength']['rule'][1],
				);
			}

			if(isset($values['between'])) {
				if(	isset($values['between']['rule'][1]) ||
					isset($values['between']['rule'][2]) ) {
					$cols[] = array(
					'type' => 'length',
					'name' => $field,
					'min' => $values['between']['rule'][1],
					'max' => $values['between']['rule'][2]
				);
				} else {
					$cols[] = array(
						'type' => 'length',
						'name' => $field,
					);
				}
			}

			//TODO there is no alpha in cakephp
			if(isset($values['alpha'])) {
				$cols[] = array(
					'type' => 'format',
					'name' => $field,
					'matcher' => $this->formater['alpha'],
				);
			}

			if(isset($values['alphaNumeric'])) {
				$cols[] = array(
					'type' => 'format',
					'name' => $field,
					'matcher' => $this->formater['alphanum'],
				);
			}

			if(isset($values['email'])) {
				$cols[] = array(
					'type' => 'format',
					'name' => $field,
					'matcher' => $this->formater['email'],
				);
			}

			if(isset($values['url'])) {
				$cols[] = array(
					'type' => 'format',
					'name' => $field,
					'matcher' => $this->formater['url'],
				);
			}

			//  numberformat = precision, min, max
			if(isset($values['decimal'])) {
				if(isset($values['decimal']['rule'][1])) {
					$cols[] = array(
						'type' => 'numberformat',
						'name' => $field,
						'precision' => $values['decimal']['rule'][1],
					);
				} else {
					$cols[] = array(
						'type' => 'numberformat',
						'name' => $field,
					);
				}
			}

			if(isset($values['range'])) {
				$cols[] = array(
					'type' => 'numberformat',
					'name' => $field,
					'min' => $values['range']['rule'][1],
					'max' => $values['range']['rule'][2],
				);
			}
			// extension
			if(isset($values['extension'])) {
				$cols[] = array(
					'type' => 'file',
					'name' => $field,
					//TODO 'extension' => $values['extension']['rule'][1],
				);
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
