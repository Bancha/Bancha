<?php
/**
 * AllBehaviorsTest file
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Model.Behavior
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 */

App::uses('ModelBehavior', 'Model');
App::uses('BanchaException', 'Bancha.Bancha/Exception');


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

	/**
	 * a mapping table from cake to extjs data types
	 */
	private $types = array(
		'enum'      => array('type'=>'string'),
		'integer'   => array('type'=>'int'),
		'string'    => array('type'=>'string'),
		'datetime'  => array('type'=>'date', 'dateFormat' =>'Y-m-d H:i:s'),
		'date'      => array('type'=>'date', 'dateFormat' =>'Y-m-d'),
		'float'     => array('type'=>'float'),
		'text'      => array('type'=>'string'),
		'boolean'   => array('type'=>'boolean'),
		'timestamp' => array('type'=>'date', 'dateFormat' =>'timestamp')

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
	 * since cakephp deletes $Model->data after a save action 
	 * we keep the necessary return values here, access through
	 * $Model->getLastSaveResult();
	 */
	private $result = array();
	
	/**
	 * the default behavor configuration
	 */
	private $_defaults = array(
		/*
		 * If true the model also saves and validates records with missing
		 * fields, like ExtJS is providing for edit operations.
		 * If you set this to false please use $Model->saveFields($data,$options)
		 * to save edit-data from extjs.
		 */
		'useOnlyDefinedFields' => true,
	);
	/**
	 * Sets up the BanchaRemotable behavior. For config options see 
	 * http://docs.banchaproject.org/resources/Advanced-Configurations.html#bancharemotablebehavior-configurations
	 *
	 * @param Model $Model instance of model
	 * @param array $config array of configuration settings.
	 * @return void
	 */
	public function setup(Model $Model, $settings = array()) {
		// apply configs
		if(!is_array($settings)) {
			throw new CakeException("Bancha: The BanchaRemotableBehavior currently only supports an array of options as configuration");
		}
		$settings = array_merge($this->_defaults, $settings);
		$this->settings[$Model->alias] = $settings;
	}

	/**
	 * Extracts all metadata which should be shared with the ExtJS frontend
	 *
	 * @param Model $Model instance of model
	 * @return array all the metadata as array
	 */
	public function extractBanchaMetaData(Model $Model) {

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


		$fields = $this->getColumnTypes($Model);
		$validations = $this->getValidations($Model);
		$associations = $this->getAssociated($Model);
		$sorters = $this->getSorters($Model);

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
		if ($required && $upload_info['size'] == 0) {
				return false;
		}

		// Check for Basic PHP file errors.
		if ($upload_info['error'] !== 0) {
			return false;
		}

		// Finally, use PHP's own file validation method.
		return is_uploaded_file($upload_info['tmp_name']);
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
	 *	    {type: 'hasMany',   model: 'Bancha.model.Post',    foreignKey: 'post_id',    name: 'posts',    getterName: 'posts',    setterName: 'setPosts'},
	 *	    {type: 'hasMany',   model: 'Bancha.model.Comment', foreignKey: 'comment_id', name: 'comments', getterName: 'comments', setterName: 'setComments'},
	 *	    {type: 'belongsTo', model: 'Bancha.model.User',    foreignKey: 'user_id',    name: 'user',     getterName: 'getUser',  setterName: 'setUser'}
	 *   ]
	 * </code>
	 *   
	 *   (source http://docs.sencha.com/ext-js/4-0/#/api/Ext.data.Model)
	 *   
	 *   in cakephp all association types are a property on the model containing a full configuration, like
	 *   <code> Array ( [Article] => Array ( [className] => Article [foreignKey] => user_id [dependent] => 
	 *          [conditions] => [fields] => [order] => [limit] => [offset] => [exclusive] => [finderQuery] => 
	 *          [counterQuery] => ) )</code>
	 *
	 * @param Model $Model instance of model
	 * @return Array An array of ExtJS/Sencha Touch association definitions
	 */
	public function getAssociated(Model $Model) {
		$assocTypes = $Model->associations();
		$assocs = array();
		foreach ($assocTypes as $type) {
			foreach($Model->{$type} as $modelName => $config) {
				if($type != 'hasAndBelongsToMany') { // extjs doesn't support hasAndBelongsToMany
					
					//generate the name to retrieve associations
					$name = ($type == 'hasMany') ? Inflector::pluralize($modelName) : $modelName;

					$assocs[] = array(
						'type' => $type, 
						'model' => 'Bancha.model.'.$config['className'], 
						'foreignKey' => $config['foreignKey'],
						'getterName' => ($type == 'hasMany') ? lcfirst($name) : 'get'.$name,
						'setterName' => 'set'.$name,
						'name' => lcfirst($name)
						);
				}
			}
		}
		return $assocs;
	}

	/**
	 * return the model columns as ExtJS Fields
	 *
	 * should look like
	 *
	 * 'User', {
	 *   fields: [
	 *     {name: 'id', type: 'int', allowNull:true, default:''},
	 *     {name: 'name', type: 'string', allowNull:true, default:''}
	 *   ]
	 * }
	 *
	 * @param Model $Model instance of model
	 * @return Array An array of ExtJS/Sencha Touch model field definitions
	 */
	private function getColumnTypes(Model $Model) {
		$schema = $Model->schema();
		$fields = array();

		// add all database fields
		foreach ($schema as $field => $fieldSchema) {
			array_push($fields, $this->getColumnType($Model, $field, $fieldSchema));
		}

		// add virtual fields
		foreach ($Model->virtualFields as $field => $sql) {
			array_push($fields, array(
				'name' => $field,
				'type' => 'auto', // we can't guess the type here
				'persist' => false // nothing to save here
			));
		}

		return $fields;
	}
	/**
	 * @see getColumnTypes
	 */
	private function getColumnType(Model $Model, $field, $fieldSchema) {

		// handle mysql enum field
		$type = $fieldSchema['type'];
		if(substr($type,0,4) == 'enum') {
			// find all possible options
			preg_match_all("/'(.*?)'/", $type, $enums);

			// add a new validation rule (only during api call)
			// in a 2.0 and 2.1 compatible way
			if(!isset($Model->validate[$field])) {
				$Model->validate[$field] = array();
			}
			$Model->validate[$field]['inList'] = array(
				'rule' => array('inList', $enums[1])
			);

			// to back to generic behavior
			$type = 'enum';
		}

		// handle normal fields
		return array_merge(
			array(
				'name' => $field,
				'allowNull' => $fieldSchema['null'],
				'defaultValue' => (!$fieldSchema['null'] && $fieldSchema['default']===null) ? 
									'' : $fieldSchema['default'] // if null is not allowed fall back to ''
				), 
			isset($this->types[$type]) ? $this->types[$type] : array('type'=>'auto'));
	}

	/**
	 * Returns an ExtJS formated array of field names, validation types and constraints.
	 *
	 * @param Model $Model instance of model
	 * @return Ext.data.validations rules
	 */
	private function getValidations(Model $Model) {
		if (empty($Model->validate)) {
			//some testcases fail with this
			//trigger_error(__d('cake_dev', '(Model::getColumnTypes) Unable to build model field data. If you are using a model without a database table, try implementing schema()'), E_USER_WARNING);
		}
		$cols = array();
		foreach ($Model->validate as $field => $values) {
			
			// cake also supports a simple structure, like:
			// http://book.cakephp.org/2.0/en/models/data-validation.html#simple-rules
			// so to support that as well, transform it:
			if(is_string($values)) {
				$values = array(
					$values => array('rule' => $values));
			}

			// and now add support for even another structure
			// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
			if(isset($values['rule'])) {
				$values = array(
					$values['rule'] => $values);
			}


			// no check for rules


			// check if the input is required
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
					'field' => $field,
				);
			}

			// isUnique can only be tested on the server, 
			// so we would need some business logic for that
			// as well, maybe integrate in Bancha Scaffold

			if(isset($values['equalTo'])) {
				$cols[] = array(
					'type' => 'inclusion',
					'field' => $field,
					'list' => array($values['equalTo']['rule'][1])
				);
			}

			if(isset($values['boolean'])) {
				$cols[] = array(
					'type' => 'inclusion',
					'field' => $field,
					'list' => array(true,false,'0','1',0,1)
				);
			}

			if(isset($values['inList'])) {
				$cols[] = array(
					'type' => 'inclusion',
					'field' => $field,
					'list' => $values['inList']['rule'][1]
				);
			}

			if(isset($values['minLength']) || isset($values['maxLength'])) {
				$col = array(
					'type' => 'length',
					'field' => $field,
				);
				
				if(isset($values['minLength'])) {
					$col['min'] = $values['minLength']['rule'][1];
				}
				if(isset($values['maxLength'])) {
					$col['max'] = $values['maxLength']['rule'][1];
				}
				$cols[] = $col;
			}

			if(isset($values['between'])) {
				if(	isset($values['between']['rule'][1]) ||
					isset($values['between']['rule'][2]) ) {
					$cols[] = array(
						'type' => 'length',
						'field' => $field,
						'min' => $values['between']['rule'][1],
						'max' => $values['between']['rule'][2]
					);
				} else {
					$cols[] = array(
						'type' => 'length',
						'field' => $field,
					);
				}
			}

			//TODO there is no alpha in cakephp
			if(isset($values['alpha'])) {
				$cols[] = array(
					'type' => 'format',
					'field' => $field,
					'matcher' => $this->formater['alpha'],
				);
			}

			if(isset($values['alphaNumeric'])) {
				$cols[] = array(
					'type' => 'format',
					'field' => $field,
					'matcher' => $this->formater['alphanum'],
				);
			}

			if(isset($values['email'])) {
				$cols[] = array(
					'type' => 'format',
					'field' => $field,
					'matcher' => $this->formater['email'],
				);
			}

			if(isset($values['url'])) {
				$cols[] = array(
					'type' => 'format',
					'field' => $field,
					'matcher' => $this->formater['url'],
				);
			}

			// number validation rules
			// numberformat = precision, min, max
			if(isset($values['numeric']) || isset($values['naturalNumber'])) {
				$col = array(
					'type' => 'numberformat',
					'field' => $field,
				);

				if(isset($values['numeric']['precision'])) {
					$col['precision'] = $values['numeric']['precision'];
				}
				if(isset($values['naturalNumber'])) {
					$col['precision'] = 0;
				}

				if(isset($values['naturalNumber'])) {
					$col['min'] = (isset($values['naturalNumber']['rule'][1]) && $values['naturalNumber']['rule'][1]==true) ? 0 : 1;
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
							"Bancha: You are currently using the validation rule 'range' for ".$Model->name."->".$field.
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
					'field' => $field,
					'min' => $min,
					'max' => $max,
				);
			}
			// extension
			if(isset($values['extension'])) {
				$cols[] = array(
					'type' => 'file',
					'field' => $field,
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
	 * @param model $Model Model using this behavior
	 * @param boolean $created True if this save created a new record
	 */
	public function afterSave(Model $Model, $created) {
		// get all the data bancha needs for the response
		// and save it in the data property
		if($created) {
			// just add the id
			$this->result[$Model->alias] = $Model->data;
			$this->result[$Model->alias][$Model->name]['id'] = $Model->id;
		} else {
			// load the full record from the database
			$currentRecursive = $Model->recursive;
			$Model->recursive = -1;
			$this->result[$Model->alias] = $Model->read();
			$Model->recursive = $currentRecursive;
		}
		
		return true;
	}

	/**
	 * Returns the result record of the last save operation
	 * @param Model $Model the model using this behavior
	 * @return mixed $results The record data of the last saved record
	 */
	public function getLastSaveResult(Model $Model) {
		if(empty($this->result[$Model->alias])) {
			throw new BanchaException(
				'There was nothing saved to be returned. Probably this occures because the data '.
				'you send from ExtJS was malformed. Please use the Bancha.getModel(ModelName) '.
				'function to create, load and save model records. If you really have to create '.
				'your own models, make sure that the JsonWriter "root" (ExtJS) / "rootProperty" '.
				'(Sencha Touch) is set to "data".');
		}

		return $this->result[$Model->alias];
	}
	
	/**
	 * Builds a field list with all defined fields
	 *
	 * @param Model $Model the model using this behavior
	 */
	private function buildFieldList(Model $Model) {
		// Make a quick quick check if the data is in the right format
		if(isset($Model->data[$Model->name][0]) && is_array($Model->data[$Model->name][0])) {
			throw new BanchaException(
				'The data to be saved seems malformed. Probably this occures because you send '.
				'from your own model or you one save invokation. Please use the Bancha.getModel(ModelName) '.
				'function to create, load and save model records. If you really have to create '.
				'your own models, make sure that the JsonWriter "root" (ExtJS) / "rootProperty" '.
				'(Sencha Touch) is set to "data". <br /><br />'.
				'Got following data to save: <br />'.print_r($Model->data,true));
		}
		// More extensive data validation
		// For performance reasons this is just done in debug mode
		if(Configure::read('debug') == 2) {
			$valid = false;
			$fields = $Model->getColumnTypes();
			// check if at least one field is saved to the database
			try {
				foreach($fields as $field => $type) {
					if(array_key_exists($field, $Model->data[$Model->name])) {
						$valid=true;
						break;
					}
				}
			} catch (Exception $e) {
				throw new BanchaException(
					'Caught exception: ' . $e->getMessage() . ' <br />' .
					'Bancha couldn\'t find any fields. This is usually because the Model is incorrectly designed. ' .
					'Check your model <br /><br /><pre>'.print_r($Model->data,true).'</pre>'
				);
			}
			if(!$valid) {
				throw new BanchaException(
					'Could nto find even one model field to save to database. Probably this occurs '.
					'because you send from your own model or you one save invocation. Please use the '.
					'Bancha.getModel(ModelName) function to create, load and save model records. If '.
					'you really have to create your own models, make sure that the JsonWriter "root" (ExtJS) / "rootProperty" '.
					'(Sencha Touch) is set to "data". <br /><br />'.
					'Got following data to save: <br />'.print_r($Model->data,true));
			}
		} //eo debugging checks

		return array_keys(isset($Model->data[$Model->name]) ? $Model->data[$Model->name] : $data);
	}
	/**
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param Model $Model the model using this behavior
	 * @param Array $options Options passed from model::save(), see $options of model::save().
	 * @return Boolean True if validate operation should continue, false to abort
	 */
	public function beforeValidate(Model $Model, $options = array()) {
		if($this->settings[$Model->alias]['useOnlyDefinedFields'] && !empty($Model->data[$Model->name])) {
			// if not yet defined, create a field list to validate only the changes (empty records will still invalidate)
			$Model->whitelist = empty($options['fieldList']) ? $this->buildFieldList($Model) : $options['fieldList']; // TODO how to not overwrite the whitelist?
		}
		
		// start validating data
		return true;
	}
	/**
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param Model $Model the model using this behavior
	 * @param Array $options
	 * @return Boolean True if the operation should continue, false if it should abort
	 */
	public function beforeSave(Model $Model, $options = array()) {
		if($this->settings[$Model->alias]['useOnlyDefinedFields']) {
			// if not yet defined, create a field list to save only the changes
			$options['fieldList'] = empty($options['fieldList']) ? $this->buildFieldList($Model) : $options['fieldList'];
		}

		// start saving data
		return true;
	}
	/**
	 * Saves a records, either add or edit. 
	 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
	 * 
	 * @param Model $Model the model using this behavior
	 * @param Array $data the data to save (first user argument)
	 * @param Array $options the save options
	 * @return Array|Boolean The result of the save operation
	 */
	public function saveFields(Model $Model, $data=null, $options=array()) {
		// overwrite config for this commit
		$config = $this->settings[$Model->alias]['useOnlyDefinedFields'];
		$this->settings[$Model->alias]['useOnlyDefinedFields'] = true;
		
		// this should never be the case, cause Bancha cannot handle validation errors currently
		// We expect to automatically send validation errors to the client in the right format in version 1.1
		if($data) {
			$Model->set($data);
		}
		if(!$Model->validates()) {
			$msg =  "The record doesn't validate. Since Bancha can't send validation errors to the ".
					"client yet, please handle this in your application stack.";
			if(Configure::read('debug') > 0) {
				$msg .= "<br/><br/><pre>Validation Errors:\n".print_r($Model->invalidFields(),true)."</pre>";
			}
			throw new BadRequestException($msg);
		}
		
		$result[$Model->alias] = $Model->save($Model->data,$options);
		
		// set back
		$this->settings[$Model->alias]['useOnlyDefinedFields'] = $config;
		return $result[$Model->alias];
	}
	
	/**
	 * Commits a save operation for all changed data and 
	 * returns the result in an extjs format
	 * for return value see also getLastSaveResult()
	 * 
	 * @param Model $Model the model is always the first param (cake does this automatically)
	 * @param $data the data to save, first function argument
	 */
	public function saveFieldsAndReturn(Model $Model, $data=null) {
		// save
		$this->saveFields($Model,$data);
		
		// return ext-formated result
		return $this->getLastSaveResult($Model);
	}
	
	/**
	 * convenience methods, just delete and then return $Model->getLastSaveResult();
	 *
	 * @param Model $Model the model using this behavior
	 * @®eturn Array|Boolean the latest save result
	 */
	public function deleteAndReturn(Model $Model) {
		if (!$Model->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$Model->delete();
		return $this->getLastSaveResult($Model);
	}
	
	public function afterDelete(Model $Model) {
		// if no exception was thrown so far the request was successfull
		$this->result[$Model->alias] = true;
	}
	
/**
 * Returns an ExtJS formated array describing sortable fields
 * this is '$order' in cakephp
 *
 * @param Model $Model the model using this behavior
 * @return array ExtJS formated  { property: 'name', direction: 'ASC'	}
 */
	private function getSorters(Model $Model) {
		// TODO TechDocu: only arrays are allowed as $order
		$sorters = array();
		if ( is_array($Model->order) ) {
			foreach($Model->order as $key => $value) {
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
