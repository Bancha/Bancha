<?php
/**
 * AllBehaviorsTest file
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Model.Behavior
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 */

App::uses('ModelBehavior', 'Model');
App::uses('BanchaException', 'Bancha.Bancha/Exception');
App::uses('CakeSenchaDataMapper', 'Bancha.Bancha');


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
 * @package       Bancha.Model.Behavior
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @since         Bancha v 0.9.0
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
		'time'      => array('type'=>'date', 'dateFormat' =>'H:i:s'),
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
		/**
		 * If true, the model also saves and validates records with missing
		 * fields, like Ext JS/Sencha Touch is providing for edit operations.
		 * If you set this to false, please use $Model->saveFields($data,$options)
		 * to save edit-data from Ext JS/Sencha Touch.
		 *
		 * See also:
		 * http://banchaproject.org/documentation-pro-models-validation-rules.html#useOnlyDefinedFields
		 *
		 * @var boolean
		 */
		'useOnlyDefinedFields' => true,
		/**
		 * Defined which field should be exposed. If defined, these fields
		 * will be taken as a base of fields to expose, the excludeFields
		 * config will still be applied.
		 *
		 * See also:
		 * http://banchaproject.org/documentation-pro-models-exposed-and-hidden-fields.htmls
		 *
		 * @var string[]|null
		 */
		'exposedFields' => null,
		/**
		 * Defined which fields should never be exposed. This config overrules
		 * exposedFields.
		 *
		 * See also:
		 * http://banchaproject.org/documentation-pro-models-exposed-and-hidden-fields.htmls
		 *
		 * @var string[]
		 */
		'excludedFields' => array()
	);
	/**
	 * Sets up the BanchaRemotable behavior. For config options see above.
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

		//<bancha-basic>
		if(Configure::read('Bancha.isPro')==false) {
			return array();
		}
		//</bancha-basic>
		//<bancha-pro>
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
		//</bancha-pro>
	}

	/**
	 * Calculates an array of all exposed model fields.
	 * @param Model $Model The model of the field to check
	 * @return string[] Array of model field names (as strings)
	 */
	public function _getExposedFields($Model) {
		$settings = $this->settings[$Model->alias];

		// cache pattern
		if(isset($settings['_computedExposedFields'])) {
			return $settings['_computedExposedFields'];
		}

		// compute
		$fields = array_merge(
			array_keys($Model->schema()), // first get all model fields
			array_keys($Model->virtualFields)); // and add all virtual fields


		// if exposedFields is an array, match
		if(isset($settings['exposedFields']) && is_array($settings['exposedFields'])) {
			// remove all fields which are not in exposedFields
			$fields = array_intersect($fields, $settings['exposedFields']);

			// In debug mode check if all exposed fields are valid
			if(Configure::read('debug')>0 && (count($fields)<count($settings['exposedFields']))) {
				$wrongNames = array_diff($settings['exposedFields'], $fields);
				throw new CakeException(
					"Bancha: You have configured the BanchaRemotable to expose following fields for ".$Model->name.
					" which do not exist in the schema: ".print_r($wrongNames,true).
					"\nPlease remove them or fix your schema.\n".
					"This error is only displayed in debug mode."
				);
			}
		}

		// if excludedFields is an array, exclude those
		if(isset($settings['excludedFields']) && is_array($settings['excludedFields'])) {

			// In debug mode check if all exposed fields are valid
			if(Configure::read('debug')>0) {
				$wrongNames = array_diff($settings['excludedFields'], $fields);
				if(count($wrongNames)) {
					throw new CakeException(
						"Bancha: You have configured the BanchaRemotable to exclude following fields for ".$Model->name.
						" which do not exist in the schema: ".print_r($wrongNames,true).
						"\nPlease remove them or fix your schema.\n".
						"This error is only displayed in debug mode."
					);
				}
			}

			// remove all fields which are in excludedFields
			$fields = array_diff($fields, $settings['excludedFields']);
		}

		// fix the indexes
		$fields = array_values($fields);

		// set cache and return
		$settings['_computedExposedFields'] = $fields;
		return $fields;
	}
	/**
	 * Returns true if the field is visible to the ExtJS/Sencha Touch frontend.
	 * @param Model $Model The model of the field to check
	 * @param string $fieldName The name of the field (see schema)
	 * @return boolean True if it is exposed
	 */
	public function isExposedField($Model, $fieldName) {
		return in_array($fieldName, $this->_getExposedFields($Model));
	}

	/**
	 * Filters all non-exposed fields from an CakePHP dataset. This
	 * includes a single record, multiple records and nested data. // TODO
	 *
	 * @param  array $records The records to filter
	 * @return array          Returns data in the same structure as input, but filtered.
	 */
	public function filterRecords($Model, $data) {

		$mapper = new CakeSenchaDataMapper($data, $Model->name);

		if($mapper->isSingleRecord()) {
			// filter the data of the single record
			$data[$Model->name] = $this->filterRecord($Model, $data[$Model->name]);
		} else if($mapper->isRecordSet()) {
			// handle each record
			foreach ($data as $key => $record) {
				$data[$key][$Model->name] = $this->filterRecord($Model, $record[$Model->name]);
			}
		} else if($mapper->isPaginatedSet()) {

			// the records have standard cake handle each record
			foreach ($data['records'] as $key => $record) {
				$data['records'][$key][$Model->name] = $this->filterRecord($Model, $record[$Model->name]);
			}
		}

		return $data;
	}

	/**
	 * Filters all non-exposed fields from an indivudal record.
	 *
	 * This function expects only the record data in the following
	 * form:
	 *     array(
	 *         'fieldName'  => 'fieldValue',
	 *         'fieldName2' => 'fieldValue2',
	 *     )
	 *
	 * @param  array $recData The record data to filter
	 * @return array          Returns data in the same structure as input, but filtered.
	 */
	public function filterRecord($Model, $recData) {

		$result = array();
		foreach ($this->_getExposedFields($Model) as $fieldName) {
			if(isset($recData[$fieldName])) {
				// transforms integers to type int
				// This is necessary when a form loads fields like user_id,
				// which need to be a integer
				if(ctype_digit($recData[$fieldName])) { // is integer string
					// this looks a bit hacky, but speed is more important then
					// doing his by checking over the models schema type
					$recData[$fieldName] = (int) $recData[$fieldName];
				}
				$result[$fieldName] = $recData[$fieldName];
			}
		}

		return $result;
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
		foreach ($assocTypes as $type) { // only 3 types
			if($type == 'hasAndBelongsToMany') {
				// ExtJS/Sencha Touch doesn't support hasAndBelongsToMany
				continue;
			}
			foreach($Model->{$type} as $modelName => $config) {

				//generate the name to retrieve associations
				$name = ($type == 'hasMany') ? Inflector::pluralize($modelName) : $modelName;

				if($type == 'belongsTo' && !$this->isExposedField($Model, $config['foreignKey'])) {
					// this field is hidden from ExtJS/Sencha Touch, so also hide the association
					continue;
				}

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
		return $assocs;
	}

	/**
	 * Return the model column schema as ExtJS/Sencha Touch structure.
	 *
	 * Example:
	 *     [
	 *       {name: 'id', type: 'int', allowNull:true, default:''},
	 *       {name: 'name', type: 'string', allowNull:true, default:''}
	 *     ]
	 *
	 * @param Model $Model instance of model
	 * @return Array An array of ExtJS/Sencha Touch model field definitions
	 */
	public function getColumnTypes(Model $Model) {
		$schema = $Model->schema();
		$fields = array();

		// add all database fields
		foreach ($schema as $field => $fieldSchema) {
			if($this->isExposedField($Model, $field)) {
				array_push($fields, $this->getColumnType($Model, $field, $fieldSchema));
			}
		}

		// add virtual fields
		foreach ($Model->virtualFields as $field => $sql) {
			if($this->isExposedField($Model, $field)) {
				array_push($fields, array(
					'name' => $field,
					'type' => 'auto', // we can't guess the type here
					'persist' => false // nothing to save here
				));
			}
		}

		return $fields;
	}

	/**
	 * @see #getColumnTypes
	 *
	 * The model is only used for MySQL enum support
	 */
	public function getColumnType(Model $Model, $fieldName, $fieldSchema) {

		// handle mysql enum field
		$type = $fieldSchema['type'];
		if(substr($type,0,4) == 'enum') {
			// find all possible options
			preg_match_all("/'(.*?)'/", $type, $enums);

			// add a new validation rule (only during api call)
			// in a 2.0 and 2.1 compatible way
			if(!isset($Model->validate[$fieldName])) {
				$Model->validate[$fieldName] = array();
			}
			$Model->validate[$fieldName]['inList'] = array(
				'rule' => array('inList', $enums[1])
			);

			// to back to generic behavior
			$type = 'enum';
		}

		// handle mysql timestamp default value
		if($type=='timestamp' && $fieldSchema['default']=='CURRENT_TIMESTAMP') {
			$fieldSchema['null'] = true;
			$fieldSchema['default'] = '';
		}

		// handle normal fields
		return array_merge(
			array(
				'name' => $fieldName,
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
	 * @return array Ext.data.validations rules
	 */
	public function getValidations(Model $Model) {
		$rules = array();

		// only use validation rules for exposed fields
		foreach ($Model->validate as $fieldName => $fieldRules) {
			if($this->isExposedField($Model, $fieldName)) {
				$rules[$fieldName] = $fieldRules;
			}
		}

		// normalize
		$rules = $this->normalizeValidationRules($rules);

		// transform rules
		$cols = array();
		foreach ($rules as $fieldName => $fieldRules) {
			$cols = array_merge($cols, $this->getValidationRulesForField($fieldName, $fieldRules));
		}

		return $cols;
	}


	public function normalizeValidationRules($rules) {

		foreach ($rules as $fieldName => $fieldRules) {

			$normalizedFieldRules = array();

			if(is_string($fieldRules) || isset($fieldRules['rule'])) {
				// this is only one rule
				$fieldRule = $this->normalizeValidationRule($fieldRules);
				$normalizedFieldRules[$fieldRule['rule'][0]] = $fieldRule;
			} else {
				// Transform multiple rules per field into our normalized structure
				// http://book.cakephp.org/2.0/en/models/data-validation.html#multiple-rules-per-field
				foreach ($fieldRules as $customRuleName => $fieldRule) {
					$fieldRule = $this->normalizeValidationRule($fieldRule);
					$normalizedFieldRules[$fieldRule['rule'][0]] = $fieldRule;
				}
			}

			$rules[$fieldName] = $normalizedFieldRules;
		}

		return $rules;
	}

	private function normalizeValidationRule($fieldRule) {

		// Transform simple rules into our normalized structure
		// http://book.cakephp.org/2.0/en/models/data-validation.html#simple-rules
		if(is_string($fieldRule)) {
			$fieldRule = array(
				'rule' => array($fieldRule)
			);
		}

		// Transform one rule per field with an string rule into our normalized structure
		// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
		if(isset($fieldRule['rule']) && is_string($fieldRule['rule'])) {
			$fieldRule['rule'] = array($fieldRule['rule']);
		}

		// The case below is as we expect it
		// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
		// if(isset($fieldRule['rule']) && is_array($fieldRule['rule'])) {

		return $fieldRule;
	}

	public function getValidationRulesForField($fieldName, $rules) {
		$cols = array();

		// check if the input is required
		$presence = false;
		foreach($rules as $rule) {
			if((isset($rule['required']) && $rule['required']) ||
			   (isset($rule['allowEmpty']) && !$rule['allowEmpty'])) {
				$presence = true;
				break;
			}
		}
		if(isset($rules['notEmpty']) || $presence) {
			$cols[] = array(
				'type' => 'presence',
				'field' => $fieldName,
			);
		}

		// isUnique can only be tested on the server,
		// so we would need some business logic for that
		// as well, maybe integrate in Bancha Scaffold

		if(isset($rules['equalTo'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => array($rules['equalTo']['rule'][1])
			);
		}

		if(isset($rules['boolean'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => array(true,false,'0','1',0,1)
			);
		}

		if(isset($rules['inList'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => $rules['inList']['rule'][1]
			);
		}

		if(isset($rules['minLength']) || isset($rules['maxLength'])) {
			$col = array(
				'type' => 'length',
				'field' => $fieldName,
			);

			if(isset($rules['minLength'])) {
				$col['min'] = $rules['minLength']['rule'][1];
			}
			if(isset($rules['maxLength'])) {
				$col['max'] = $rules['maxLength']['rule'][1];
			}
			$cols[] = $col;
		}

		if(isset($rules['between'])) {
			if(	isset($rules['between']['rule'][1]) ||
				isset($rules['between']['rule'][2]) ) {
				$cols[] = array(
					'type' => 'length',
					'field' => $fieldName,
					'min' => $rules['between']['rule'][1],
					'max' => $rules['between']['rule'][2]
				);
			} else {
				$cols[] = array(
					'type' => 'length',
					'field' => $fieldName,
				);
			}
		}

		//TODO there is no alpha in cakephp
		if(isset($rules['alpha'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->formater['alpha'],
			);
		}

		if(isset($rules['alphaNumeric'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->formater['alphanum'],
			);
		}

		if(isset($rules['email'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->formater['email'],
			);
		}

		if(isset($rules['url'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->formater['url'],
			);
		}

		// extension
		if(isset($rules['extension'])) {
			$cols[] = array(
				'type' => 'file',
				'field' => $fieldName,
				'extension' => $rules['extension']['rule'][1],
			);
		}

		// number validation rules
		$setNumberRule = false; // collect all together
		$numberRule = array(
			'type' => 'numberformat',
			'field' => $fieldName,
		);

		// numberformat = precision, min, max
		if(isset($rules['numeric']) || isset($rules['naturalNumber'])) {
			if(isset($rules['numeric']['precision'])) {
				$numberRule['precision'] = $rules['numeric']['precision'];
			}
			if(isset($rules['naturalNumber'])) {
				$numberRule['precision'] = 0;
			}

			if(isset($rules['naturalNumber'])) {
				$numberRule['min'] = (isset($rules['naturalNumber']['rule'][1]) && $rules['naturalNumber']['rule'][1]==true) ? 0 : 1;
			}

			$setNumberRule = true;
		}

		if(isset($rules['range'])) {
			// this rule is a bit ambiguous in cake, it tests like this:
			// return ($check > $lower && $check < $upper);
			// since ext understands it like this:
			// return ($check >= $lower && $check <= $upper);
			// we have to change the value
			$min = $rules['range']['rule'][1];
			$max = $rules['range']['rule'][2];

			if(isset($rules['numeric']['precision'])) {
				// increment/decrease by the smallest possible value
				$amount = 1*pow(10,-$rules['numeric']['precision']);
				$min += $amount;
				$max -= $amount;
			} else {

				// if debug tell dev about problem
				if(Configure::read('debug')>0) {
					throw new CakeException(
						"Bancha: You are currently using the validation rule 'range' for the model field ".$fieldName.
						". Please also define the numeric rule with the appropriate precision, otherwise Bancha can't exactly ".
						"map the validation rules. \nUsage: array('rule' => array('numeric'),'precision'=> ? ) \n".
						"This error is only displayed in debug mode."
					);
				}

				// best guess
				$min += 1;
				$max += 1;
			}

			// set min and max values
			$numberRule['min'] = $min;
			$numberRule['max'] = $max;

			$setNumberRule = true;
		}

		if($setNumberRule) {
			$cols[] = $numberRule;
		}

		return $cols;
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
			// Setting recursive to -1 may result in an error if the virtual field uses associated data
			// On the other hand not setting the recursion to -1 has some negative performance impacts
			// $currentRecursive = $Model->recursive;
			// $Model->recursive = -1;
			$this->result[$Model->alias] = $Model->read();
			// $Model->recursive = $currentRecursive;
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
			// there were some validation errors, send those
			if(!$Model->validates()) {
				$msg =  "The record doesn't validate. Since Bancha can't send validation errors to the ".
						"client yet, please handle this in your application stack.";
				if(Configure::read('debug') > 0) {
					$msg .= "<br/><br/><pre>Validation Errors:\n".print_r($Model->invalidFields(),true)."</pre>";
				}
				throw new BadRequestException($msg);
			}

			// otherwise send error
			throw new BanchaException(
				'There was nothing saved to be returned. Probably this occures because the data '.
				'you send from ExtJS was malformed. Please use the Bancha.model.ModelName '.
				'model to create, load and save model records. If you really have to create '.
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
				'from your own model or you one save invokation. Please use the Bancha.model.ModelName '.
				'model to create, load and save model records. If you really have to create '.
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
					'You try to save a record, but Bancha is not able to find the data. Bancha could '.
					'not find even one model field in the send data. Probably this occurs because you '.
					'saved a record from your own model with a wrong configuration. Please use the '.
					'Bancha.model.ModelName model to create, load and save model records. If '.
					'you really have to create your own models, make sure that the JsonWriter property '.
					'"root" (ExtJS) / "rootProperty" (Sencha Touch) is set to "data". <br /><br />'.
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
	public function getSorters(Model $Model) {
		$sorters = array();

		if(empty($Model->order)) {
			return $sorters;
		}

		if(is_string($Model->order)) {
			$order = trim($Model->order);

			if(strpos($order, '.')===false) {
				// this is just the field name
				$fieldName = $order;
				$direction = 'ASC';

			} else if(strpos($order, ' ')===false) {
				// this has a model name and a field name, but no direction
				$modelName = strtok($order, ".");
				$fieldName = strtok(" ");
				$direction = 'ASC';
			} else {
				// this has a model name, a field name and a direction
				$modelName = strtok($order, ".");
				$fieldName = strtok(" ");
				$direction = strtoupper(substr($order, strpos($order, ' ')+1));
			}
			array_push($sorters, array( 'property' => $fieldName, 'direction' => $direction));

		} else if(is_array($Model->order)) {
			foreach($Model->order as $key => $direction) {
				$modelName = strtok($key, ".");
				$fieldName = strtok(".");
				array_push($sorters, array( 'property' => $fieldName, 'direction' => $direction));
			}

		} else {
			throw new CakeException("The CakePHP ".$Model->alias." model configuration for order needs to be a string or array, instead got ".gettype($Model->order));
		}
		return $sorters;
	}

}
