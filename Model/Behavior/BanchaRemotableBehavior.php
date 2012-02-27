<?php
/**
 * AllBehaviorsTest file
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Model.Behavior
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 */

App::uses('ModelBehavior', 'Model');

// backwards compability with 5.2
if ( false === function_exists('lcfirst') ) {
	function lcfirst( $str ) { return (string)(strtolower(substr($str,0,1)).substr($str,1)); }
}

/**
 * BanchaBahavior
 * 
 * The behaviour extends remotly available models with the 
 * necessary functions to use Bancha.
 *
 * @package    Bancha
 * @subpackage Model.Behavior
 */
class BanchaRemotableBehavior extends ModelBehavior {
	private $schema;
	private $model;

	/**
	 * a mapping table from cake to extjs data types
	 */
	private $types = array(
		"integer" => "int",
		"string" => "string",
		"datetime" => "date",
		"date" => "date",
		"float" => "float",
		"text" => "string",
		"boolean" => "boolean",
		"timestamp" => "date"

	);

	/**
	 * a mapping table from cake to extjs validation rules
	 */
	private $formater = array(
		'alpha' => 'banchaAlpha',
		'alphanum' => 'banchaAlphanum',
		'email' => 'banchaEmail',
		'url' => 'banchaUrl',
	);

	/**
	 * since cakephp deletes $model->data after a save action 
	 * we keep the necessary return values here, access through
	 * $model->getLastSaveResult();
	 */
	private $result = null;
	
	/**
	 * the default behavor configuration
	 */
	private $_defaults = array(
		/*
		 * If true the model also saves and validates records with missing
		 * fields, like ExtJS is providing for edit operations.
		 * If you set this to false please use $model->saveFields($data,$options)
		 * to save edit-data from extjs.
		 */
		'useOnlyDefinedFields' => true,
	);
/**
 * Sets up the BanchaRemotable behavior. For config options see 
 * https://github.com/Bancha/Bancha/wiki/BanchaRemotableBehavior-Configurations
 *
 * @param object $Model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(&$Model, $config = array()) {
		$this->model = $Model;
		$this->schema = $Model->schema();
		
		// apply configs
		if(!is_array($config)) {
			throw new CakeException("Bancha: The BanchaRemotableBehavior currently only supports an array of options as configuration");
		}
		$settings = array_merge($this->_defaults, $config);
		$this->settings[$this->model->alias] = $settings;
	}

	/**
	 * set the model explicit as cakephp does not instantiate the behavior for each model
	 */
	public function setBehaviorModel(&$Model) {
		$this->model = $Model;
		$this->schema = $Model->schema();
	}

/**
 * Extracts all metadata which should be shared with the ExtJS frontend
 *
 * @param AppModel $model
 * @return array all the metadata as array
 */
	public function extractBanchaMetaData() {

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
	public function validateFile($data, $required = false) {
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
	public function file($check) {
		return true;
	}

/**
 * Return the Associations as ExtJS-Assoc Model
 * should look like this:
 * <code>
 * associations: [
 *	    {type: 'hasMany', model: 'Post',	name: 'posts'},
 *	    {type: 'hasMany', model: 'Comment', name: 'comments'}
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
			if($value != 'hasAndBelongsToMany') { // extjs doesn't support hasAndBelongsToMany
				$name = lcfirst(Inflector::pluralize($field)); //generate a handy name
				$return[] = array ('type' => $value, 'model' => $field, 'name' => $name);
			}
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
			
			// check is the input is required
			$presence = false;
			foreach($values as $rule) {
				if((isset($rule['required']) && $rule['required']) ||
				   (isset($rule['allowEmpty']) && !$rule['allowEmpty'])) {
					$presence = true;
					break;
				}
			}
			if(isset($values['notempty']) || $presence) {
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
			if(isset($values['numeric'])) {
				if(isset($values['numeric']['precision'])) {
					$cols[] = array(
						'type' => 'numberformat',
						'name' => $field,
						'precision' => $values['numeric']['precision'],
					);
				} else {
					$cols[] = array(
						'type' => 'numberformat',
						'name' => $field,
					);
				}
			}
			
			if(isset($values['range'])) {
				// this rule is a bit ambiguous in cake, it tests like this: 
				// return ($check > $lower && $check < $upper);
				// since ext understands it like this:
				// return ($check >= $lower && $check <= $upper);
				// we have to change the value
				$min = $values['range']['rule'][1];
				$max = $values['range']['rule'][2];
				
				if(isset($values['numeric']['precision'])) {
					// increment/decrease by the smallest possible value
					$amount = 1*pow(10,-$values['numeric']['precision']);
					$min += $amount;
					$max -= $amount;
				} else {
					
					// if debug tell dev about problem
					if(Configure::read('debug')>0) {
						throw new CakeException(
							"Bancha: You are currently using the validation rule 'range' for ".$this->model->name."->".$field.
							". Please also define the numeric rule with the appropriate precision, otherwise Bancha can't exactly ".
							"map the validation rules. \nUsage: array('rule' => array('numeric'),'precision'=> ? ) \n".
							"This error is only displayed in debug mode."
						);
					}
					
					// best guess
					$min += 1;
					$max += 1;
				}
				$cols[] = array(
					'type' => 'numberformat',
					'name' => $field,
					'min' => $min,
					'max' => $max,
				);
			}
			// extension
			if(isset($values['extension'])) {
				$cols[] = array(
					'type' => 'file',
					'name' => $field,
					'extension' => $values['extension']['rule'][1],
				);
			}

		}
		return $cols;
	}

	/**
	 * After saving load the full record from the database to 
	 * return to the frontend
	 *
	 * @param object $model Model using this behavior
	 * @param boolean $created True if this save created a new record
	 */
	public function afterSave($model, $created) {
		// get all the data bancha needs for the response
		// and save it in the data property
		if($created) {
			// just add the id
			$this->result = $model->data;
			$this->result[$model->name]['id'] = $model->id;
		} else {
			// load the full record from the database
			$currentRecursive = $model->recursive;
			$model->recursive = -1;
			$this->result = $model->read();
			$model->recursive = $currentRecursive;
		}
		
		return true;
	}

	/**
	 * Returns the result record of the last save operation
	 * mixed $results The record data of the last saved record
	 */
	public function getLastSaveResult() {
		return $this->result;
	}
	
	/**
	 * Builds a field list with all defined fields
	 */
	private function buildFieldList($data) {
		return array_keys(isset($data[$this->model->name]) ? $data[$this->model->name] : $data);
	}
	/**
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param $model the model
	 * @param $options the validation options
	 */
	public function beforeValidate($model,$options) {
		if($this->settings[$this->model->alias]['useOnlyDefinedFields']) {
			// if not yet defined, create a field list to validate only the changes (empty records will still invalidate)
			$model->whitelist = empty($options['fieldList']) ? $this->buildFieldList($model->data) : $options['fieldList']; // TODO how to not overwrite the whitelist?
		}
		
		// start validating data
		return true;
	}
	/**
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param $model the model
	 * @param $options the save options
	 */
	public function beforeSave($model,$options) {
		if($this->settings[$this->model->alias]['useOnlyDefinedFields']) {
			// if not yet defined, create a field list to save only the changes
			$options['fieldList'] = empty($options['fieldList']) ? $this->buildFieldList($model->data) : $options['fieldList'];
		}
		
		// start saving data
		return true;
	}
	/**
	 * Saves a records, either add or edit. 
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param $model the model (set by cake)
	 * @param $data the data to save (first user argument)
	 * @param $options the save options
	 * @return returns the result of the save operation
	 */
	public function saveFields($model,$data=null,$options=array()) {
		// overwrite config for this commit
		$config = $this->settings[$this->model->alias]['useOnlyDefinedFields'];
		$this->settings[$this->model->alias]['useOnlyDefinedFields'] = true;
		
		// this should never be the case, cause Bancha cannot handle validation errors currently
		// We expect to automatically send validation errors to the client in the right format in version 1.1
		if($data) {
			$model->set($data);
		}
		if(!$model->validates()) {
			$msg =  "The record doesn't validate. Since Bancha can't send validation errors to the ".
					"client yet, please handle this in your application stack.";
			if(Configure::read('debug') > 0) {
				$msg .= "<br/><br/><pre>Validation Errors:\n".print_r($model->invalidFields(),true)."</pre>";
			}
			throw new BadRequestException($msg);
		}
		
		$result = $model->save($model->data,$options);
		
		// set back
		$this->settings[$this->model->alias]['useOnlyDefinedFields'] = $config;
		return $result;
	}
	
	/**
	 * Commits a save operation for all changed data and 
	 * returns the result in an extjs format
	 * for return value see also getLastSaveResult()
	 * 
	 * @param $model the model is always the first param (cake does this automatically)
	 * @param $data the data to save, first function argument
	 */
	public function saveFieldsAndReturn($model,$data) {
		// save
		$this->saveFields($model,$data);
		
		// return ext-formated result
		return $this->getLastSaveResult();
	}
	
	/**
	 * convenience methods, just delete and then return $this.getLastSaveResult();
	 */
	public function deleteAndReturn($model) {
		if (!$model->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$model->delete();
		return $this->getLastSaveResult();
	}
	
	public function afterDelete() {
		// if no exception was thrown so far the request was successfull
		$this->result = true;
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
