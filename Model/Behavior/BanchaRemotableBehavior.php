<?php
/**
 * BanchaRemotableBehavior file
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Model.Behavior
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 */

App::uses('ModelBehavior', 'Model');
App::uses('BanchaException', 'Bancha.Bancha/Exception');
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
 * BanchaRemotableBehavior
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
 * A mapping table from cake to Sencha data types
 * @var array
 */
	protected $_types = array(
		'enum'		=> array('type' => 'string'),
		'integer'	=> array('type' => 'int'),
		'string'	=> array('type' => 'string'),
		'datetime'	=> array('type' => 'date', 'dateFormat' => 'Y-m-d H:i:s'),
		'date'		=> array('type' => 'date', 'dateFormat' => 'Y-m-d'),
		'time'		=> array('type' => 'date', 'dateFormat' => 'H:i:s'),
		'float'		=> array('type' => 'float'),
		'text'		=> array('type' => 'string'),
		'boolean'	=> array('type' => 'boolean'),
		'timestamp'	=> array('type' => 'date', 'dateFormat' => 'timestamp')

	);

/**
 * A mapping table from cake to Sencha validation rules
 * @var array
 */
	protected $_formater = array(
		'alpha' => 'banchaAlpha',
		'alphanum' => 'banchaAlphanum',
		'email' => 'banchaEmail',
		'url' => 'banchaUrl',
	);

/**
 * Since cakephp deletes $model->data after a save action
 * we keep the necessary return values here, access through
 * $model->getLastSaveResult();
 *
 * @var array Collection of model save results
 */
	protected $_result = array();

/**
 * Default behavoir configuration
 */
	protected $_defaults = array(
		/*
		 * If true, the model also saves and validates records with missing
		 * fields, like Ext JS/Sencha Touch is providing for edit operations.
		 * If you set this to false, please use $model->saveFields($data, $options)
		 * to save edit-data from Ext JS/Sencha Touch.
		 *
		 * See also:
		 * http://bancha.io/documentation-pro-models-validation-rules.html#useOnlyDefinedFields
		 *
		 * @var boolean
		 */
		'useOnlyDefinedFields' => true,
		/*
		 * Defined which field should be exposed. If defined, these fields
		 * will be taken as a base of fields to expose, the excludeFields
		 * config will still be applied.
		 *
		 * See also:
		 * http://bancha.io/documentation-pro-models-exposed-and-hidden-fields.html
		 *
		 * @var string[]|null
		 */
		'exposedFields' => null,
		/*
		 * Defined which fields should never be exposed. This config overrules
		 * exposedFields.
		 *
		 * See also:
		 * http://bancha.io/documentation-pro-models-exposed-and-hidden-fields.html
		 *
		 * @var string[]
		 */
		'excludedFields' => array()
	);

/**
 * Collection of configurations for each model
 */
	protected $_settings = array();

/**
 * Sets up the BanchaRemotable behavior. For config options see $_defaults.
 *
 * @param Model $model    Model using this behavior
 * @param array $settings Array of configuration settings.
 * @return void
 * @throws CakeException If malformed settings are given
 */
	public function setup(Model $model, $settings = array()) {
		// apply configs
		if (!is_array($settings)) {
			throw new CakeException('Bancha: The BanchaRemotableBehavior currently only supports an array of options as configuration');
		}

		// Check that exposedFields is valid
		if (isset($settings['exposedFields']) && (!is_array($settings['exposedFields']) || count($settings['exposedFields']) == 0)) {
			throw new CakeException(
				'Bancha: The BanchaRemotableBehavior expects the exposedFields config to be null or a non-empty array, ' .
				'instead ' . print_r($settings['exposedFields'], true) . ' given for model ' . $model->name . '.'
			);
		}
		if (isset($settings['exposeFields'])) {
			throw new CakeException(
				'Bancha: You have set the BanchaRemotableBehavior config "exposeFields" for model ' .
				$model->name . ', but the config "exposedFields" (written with "d") is expected instead.'
			);
		}

		// Check that exposedFields is valid
		if (isset($settings['excludedFields']) && !is_array($settings['excludedFields'])) {
			throw new CakeException(
				'Bancha: The BanchaRemotableBehavior expects the excludedFields config to be an array, ' .
				'instead ' . print_r($settings['excludedFields'], true) . ' given for model ' . $model->name . '.'
			);
		}
		if (isset($settings['excludeFields'])) {
			throw new CakeException(
				'Bancha: You have set the BanchaRemotableBehavior config "excludeFields" for model ' .
				$model->name . ', but the config "excludedFields" (written with "d") is expected instead.'
			);
		}

		// Check if id is correctly defined
		if ($model->primaryKey !== 'id') {
			throw new CakeException(
				'Bancha currently only supports exposing models with a primary key "id" as primary models.' .
				'The model "' . $model->name . ' can still be loaded as associated data.'
			);
		}

		// setup the data
		$settings = array_merge($this->_defaults, $settings);
		$this->_settings[$model->alias] = $settings;
	}

/**
 * Extracts all metadata which should be shared with the Ext JS frontend
 *
 * @param Model $model Model using this behavior
 * @return array       All the metadata as array
 * @throws CakeException If the id field is not exposed, but exists on the model
 */
	public function extractBanchaMetaData(Model $model) {
		//<bancha-basic>
		if (Configure::read('Bancha.isPro') == false) {
			return array();
		}
		//</bancha-basic>
		//<bancha-pro>

		// Check that the id property is always exposed
		// Edge case, if the model is a hasAndBelongsToMany and doesn't have an id don't throw a warning
		if (!$this->isExposedField($model, 'id') && (bool)$model->schema('id')) {
			throw new CakeException(
				'Bancha: The ' . $model->name . ' models "id" field must always be exposed, for Sencha Touch/Ext JS ' .
				' to edit and delete data, but your BanchaRemotableBehavior config hides it.'
			);
		}

		// TODO primary wie setzen?, $model->$primaryKey contains the name of the primary key
		// Ext JS has a 'idPrimary' attribute which defaults to 'id' which IS the cakephp fieldname

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

		$fields = $this->getColumnTypes($model);
		$validations = $this->getValidations($model);
		$associations = $this->getAssociated($model);
		$sorters = $this->getSorters($model);

		$ExtMetaData = array (
			'idProperty' => 'id',
			'displayField' => $model->displayField ? $model->displayField : null,
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
 * 
 * @param Model $model Model using this behavior
 * @throws CakeException If one or more fields from $settings['exposedFields'] are not valid
 * @return string[] Array of model field names (as strings)
 */
	public function getExposedFields(Model $model) {
		$settings = $this->_settings[$model->alias];

		// cache pattern
		if (isset($settings['_computedExposedFields'])) {
			return $settings['_computedExposedFields'];
		}

		// compute
		$fields = array_merge(
			array_keys(is_array($model->schema()) ? $model->schema() : array()), // first get all model fields
			array_keys($model->virtualFields)); // and add all virtual fields

		// if exposedFields is an array, match
		if (isset($settings['exposedFields']) && is_array($settings['exposedFields'])) {
			// remove all fields which are not in exposedFields
			$fields = array_intersect($fields, $settings['exposedFields']);

			// In debug mode check if all exposed fields are valid
			if (Configure::read('debug') > 0 && (count($fields) < count($settings['exposedFields']))) {
				$wrongNames = array_diff($settings['exposedFields'], $fields);
				throw new CakeException(
					"Bancha: You have configured the BanchaRemotable to expose following fields for " .
					$model->name . " which do not exist in the schema: " . print_r($wrongNames, true) .
					"\nPlease remove them or fix your schema.\n" .
					"This error is only displayed in debug mode."
				);
			}
		}

		// if excludedFields is an array, exclude those
		if (isset($settings['excludedFields']) && is_array($settings['excludedFields'])) {

			// In debug mode check if all exposed fields are valid
			if (Configure::read('debug') > 0) {
				$wrongNames = array_diff($settings['excludedFields'], $fields);
				if (count($wrongNames)) {
					throw new CakeException(
						"Bancha: You have configured the BanchaRemotable to exclude following fields for " .
						$model->name . " which do not exist in the schema: " . print_r($wrongNames, true) .
						"\nPlease remove them or fix your schema.\n" .
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
 * 
 * @param Model $model The model of the field to check
 * @param string $fieldName The name of the field (see schema)
 * @return boolean True if it is exposed
 */
	public function isExposedField(Model $model, $fieldName) {
		return in_array($fieldName, $this->getExposedFields($model));
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
 * @param Model $model   The model of the record
 * @param array $recData The record data to filter
 * @return array         Returns data in the same structure as input, but filtered.
 */
	public function filterRecord(Model $model, array $recData) {
		// only use the exposed fields
		$result = array();
		foreach ($this->getExposedFields($model) as $fieldName) {
			if (array_key_exists($fieldName, $recData)) { // isset would ignore null value, which we want to send
				// transforms integers to type int
				// This is necessary when a form loads fields like user_id,
				// which need to be a integer
				if (ctype_digit($recData[$fieldName])) { // is integer string
					// this looks a bit hacky, but speed is more important then
					// doing his by checking over the models schema type
					$recData[$fieldName] = (int)$recData[$fieldName];
				}
				$result[$fieldName] = $recData[$fieldName];
			}
		}

		// add associated models
		$assocTypes = $model->associations();
		foreach ($assocTypes as $type) { // only 3 types
			foreach ($model->{$type} as $modelName => $config) {
				if ($type == 'belongsTo' && !$this->isExposedField($model, $config['foreignKey'])) {
					// this field is hidden from Ext JS/Sencha Touch, so also hide the associated data
					continue;
				}

				// add associated record(s)
				if (isset($recData[$modelName])) {
					$result[$modelName] = $recData[$modelName];
				}
			}
		}

		return $result;
	}

/**
 * Return the Associations as Sencha association config,
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
 * @param Model $model Model using this behavior
 * @return array An array of Ext JS/Sencha Touch association definitions
 */
	public function getAssociated(Model $model) {
		$assocTypes = $model->associations();
		$assocs = array();
		foreach ($assocTypes as $type) { // only 3 types
			if ($type == 'hasAndBelongsToMany') {
				// Ext JS/Sencha Touch doesn't support hasAndBelongsToMany
				continue;
			}
			foreach ($model->{$type} as $modelName => $config) {

				//generate the name to retrieve associations
				$name = ($type == 'hasMany') ? Inflector::pluralize($modelName) : $modelName;

				if ($type == 'belongsTo' && !$this->isExposedField($model, $config['foreignKey'])) {
					// this field is hidden from Ext JS/Sencha Touch, so also hide the association
					continue;
				}

				$assocs[] = array(
					'type' => $type,
					'model' => 'Bancha.model.' . $config['className'],
					'foreignKey' => $config['foreignKey'],
					'getterName' => ($type == 'hasMany') ? lcfirst($name) : 'get' . $name,
					'setterName' => 'set' . $name,
					'name' => lcfirst($name)
				);
			}
		}
		return $assocs;
	}

/**
 * Return the model column schema as Ext JS/Sencha Touch structure.
 *
 * Example:
 *     [
 *       {name: 'id', type: 'int', allowNull:true, default:''},
 *       {name: 'name', type: 'string', allowNull:true, default:''}
 *     ]
 *
 * @param Model $model Model using this behavior
 * @return array An array of Ext JS/Sencha Touch model field definitions
 */
	public function getColumnTypes(Model $model) {
		$schema = is_array($model->schema()) ? $model->schema() : array();
		$fields = array();

		// add all database fields
		foreach ($schema as $field => $fieldSchema) {
			if ($this->isExposedField($model, $field)) {
				array_push($fields, $this->getColumnType($model, $field, $fieldSchema));
			}
		}

		// add virtual fields
		foreach ($model->virtualFields as $field => $sql) {
			if ($this->isExposedField($model, $field)) {
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
 * The $model is only used for MySQL enum support, @see #getColumnTypes.
 * 
 * @param Model  $model       Model using this behavior
 * @param string $fieldName   The fieldname of the model
 * @param array  $fieldSchema The CakePHP field schema
 * @return array              The Sencha Touch/Ext JS column configuration
 */
	public function getColumnType(Model $model, $fieldName, array $fieldSchema) {
		// handle mysql enum field
		$type = $fieldSchema['type'];
		if (substr($type, 0, 4) == 'enum') {
			// find all possible options
			preg_match_all("/'(.*?)'/", $type, $enums);

			// add a new validation rule (only during api call)
			// in a 2.0 and 2.1 compatible way
			if (!isset($model->validate[$fieldName])) {
				$model->validate[$fieldName] = array();
			}
			$model->validate[$fieldName]['inList'] = array(
				'rule' => array('inList', $enums[1])
			);

			// to back to generic behavior
			$type = 'enum';
		}

		// handle mysql timestamp default value
		if ($type == 'timestamp' && $fieldSchema['default'] == 'CURRENT_TIMESTAMP') {
			$fieldSchema['null'] = true;
			$fieldSchema['default'] = '';
		}

		if (isset($model->Behaviors->Tree) && $model->Behaviors->Tree->settings[$model->alias]['parent'] == $fieldName) {
			// map CakePHP tree behavior parent to Sencha Touch/Ext JS parent
			// the response transformation happens in the client TreeParentIdTransformedJson writer
			return array(
				'name' => 'parentId',
				'mapping' => $model->Behaviors->Tree->settings[$model->alias]['parent'],
				'type' => 'auto',
				'allowNull' => true,
				'defaultValue' => $fieldSchema['default']
			);
		}

		// handle normal fields:

		// if default value null is not allowed fall back to empty string
		$defaultValue = (!$fieldSchema['null'] && $fieldSchema['default'] === null) ? '' : $fieldSchema['default'];

		// if default value is SQL empty string, transform
		$defaultValue = ($defaultValue === '""') ? '' : $defaultValue;

		// return config
		return array_merge(
			array(
				'name' => $fieldName,
				'allowNull' => $fieldSchema['null'],
				'defaultValue' => $defaultValue
			),
			isset($this->_types[$type]) ? $this->_types[$type] : array('type' => 'auto')
		);
	}

/**
 * Returns an Ext JS formated array of field names, validation types and constraints.
 *
 * @param Model $model Model using this behavior
 * @return array Ext.data.validations rules
 */
	public function getValidations(Model $model) {
		$rules = array();

		// only use validation rules for exposed fields
		foreach ($model->validate as $fieldName => $fieldRules) {
			if ($this->isExposedField($model, $fieldName)) {
				$rules[$fieldName] = $fieldRules;
			}
		}

		// normalize
		$rules = $this->_normalizeValidationRules($rules);

		// transform rules
		$cols = array();
		foreach ($rules as $fieldName => $fieldRules) {
			$cols = array_merge($cols, $this->_getValidationRulesForField($fieldName, $fieldRules));
		}

		return $cols;
	}

/**
 * Normalizes an array to process validation rules in a backwards compatible way.
 * 
 * @param array $rules The CakePHP validation rules to normalize
 * @return array       The normalized CakePHP validation rules
 */
	protected function _normalizeValidationRules($rules) {
		foreach ($rules as $fieldName => $fieldRules) {
			$normalizedFieldRules = array();

			if (is_string($fieldRules) || isset($fieldRules['rule'])) {
				// this is only one rule
				$fieldRule = $this->_normalizeValidationRule($fieldRules);
				$normalizedFieldRules[$fieldRule['rule'][0]] = $fieldRule;
			} else {
				// Transform multiple rules per field into our normalized structure
				// http://book.cakephp.org/2.0/en/models/data-validation.html#multiple-rules-per-field
				foreach ($fieldRules as $customRuleName => $fieldRule) {
					$fieldRule = $this->_normalizeValidationRule($fieldRule);
					$normalizedFieldRules[$fieldRule['rule'][0]] = $fieldRule;
				}
			}

			$rules[$fieldName] = $normalizedFieldRules;
		}

		return $rules;
	}

/**
 * Normalizes a process validation rule in a backwards compatible 
 * way, @see #_normalizeValidationRules
 * 
 * @param string|array $fieldRule The CakePHP validation rule to normalize
 * @return array                  The normalized rule
 */
	protected function _normalizeValidationRule($fieldRule) {
		// Transform simple rules into our normalized structure
		// http://book.cakephp.org/2.0/en/models/data-validation.html#simple-rules
		if (is_string($fieldRule)) {
			$fieldRule = array(
				'rule' => array($fieldRule)
			);
		}

		// Transform one rule per field with an string rule into our normalized structure
		// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
		if (isset($fieldRule['rule']) && is_string($fieldRule['rule'])) {
			$fieldRule['rule'] = array($fieldRule['rule']);
		}

		// The case below is as we expect it
		// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
		// if (isset($fieldRule['rule']) && is_array($fieldRule['rule'])) {

		return $fieldRule;
	}

/**
 * Retrieve the Sencha Touch/Ext JS validation rules from the CakePHP
 * schema.
 * 
 * @param string $fieldName The field name of the model
 * @param array  $rules     The CakePHP rules for the field
 * @return array            The generated Sencha Touch/Ext JS validation rules
 * @throws CakeException    if in debug mode and range validation rule is wrongly configured.
 */
	protected function _getValidationRulesForField($fieldName, $rules) {
		$cols = array();

		// check if the input is required
		$presence = false;
		foreach ($rules as $rule) {
			if ((isset($rule['required']) && $rule['required']) ||
				(isset($rule['allowEmpty']) && !$rule['allowEmpty'])) {
				$presence = true;
				break;
			}
		}
		if (isset($rules['notEmpty']) || $presence) {
			$cols[] = array(
				'type' => 'presence',
				'field' => $fieldName,
			);
		}

		// isUnique can only be tested on the server,
		// so we would need some business logic for that
		// as well, maybe integrate in Bancha Scaffold

		if (isset($rules['equalTo'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => array($rules['equalTo']['rule'][1])
			);
		}

		if (isset($rules['boolean'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => array(true, false, '0', '1', 0, 1)
			);
		}

		if (isset($rules['inList'])) {
			$cols[] = array(
				'type' => 'inclusion',
				'field' => $fieldName,
				'list' => $rules['inList']['rule'][1]
			);
		}

		if (isset($rules['minLength']) || isset($rules['maxLength'])) {
			$col = array(
				'type' => 'length',
				'field' => $fieldName,
			);

			if (isset($rules['minLength'])) {
				$col['min'] = $rules['minLength']['rule'][1];
			}
			if (isset($rules['maxLength'])) {
				$col['max'] = $rules['maxLength']['rule'][1];
			}
			$cols[] = $col;
		}

		if (isset($rules['between'])) {
			if (isset($rules['between']['rule'][1]) ||
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
		if (isset($rules['alpha'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->_formater['alpha'],
			);
		}

		if (isset($rules['alphaNumeric'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->_formater['alphanum'],
			);
		}

		if (isset($rules['email'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->_formater['email'],
			);
		}

		if (isset($rules['url'])) {
			$cols[] = array(
				'type' => 'format',
				'field' => $fieldName,
				'matcher' => $this->_formater['url'],
			);
		}

		// extension
		if (isset($rules['extension'])) {
			$cols[] = array(
				'type' => 'file',
				'field' => $fieldName,
				'extension' => $rules['extension']['rule'][1],
			);
		}

		// number validation rules
		$setNumberRule = false; // collect all together
		$numberRule = array(
			'type' => 'range',
			'field' => $fieldName,
		);

		// range = precision, min, max
		if (isset($rules['numeric']) || isset($rules['naturalNumber'])) {
			if (isset($rules['naturalNumber'])) {
				$numberRule['precision'] = 0;
			}

			if (isset($rules['numeric']['precision'])) {
				$numberRule['precision'] = $rules['numeric']['precision'];
			}

			if (isset($rules['naturalNumber'])) {
				$numberRule['min'] = (isset($rules['naturalNumber']['rule'][1]) && $rules['naturalNumber']['rule'][1] == true) ? 0 : 1;
			}

			$setNumberRule = true;
		}

		if (isset($rules['range'])) {
			// this rule is a bit ambiguous in cake, it tests like this:
			// return ($check > $lower && $check < $upper);
			// since ext understands it like this:
			// return ($check >= $lower && $check <= $upper);
			// we have to change the value
			$min = $rules['range']['rule'][1];
			$max = $rules['range']['rule'][2];

			if (isset($rules['numeric']['precision'])) {
				// increment/decrease by the smallest possible value
				$amount = 1 * pow(10, -$rules['numeric']['precision']);
				$min += $amount;
				$max -= $amount;
			} else {

				// if debug tell dev about problem
				if (Configure::read('debug') > 0) {
					throw new CakeException(
						"Bancha: You are currently using the validation rule 'range' for the model field " . $fieldName .
						". Please also define the numeric rule with the appropriate precision, otherwise Bancha can't exactly " .
						"map the validation rules. \nUsage: array('rule' => array('numeric'), 'precision'=> ? ) \n" .
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

		if ($setNumberRule) {
			$cols[] = $numberRule;
		}

		return $cols;
	}

/**
 * Prepare an array of Model's validation error messages.
 * 
 * @param model $model Model using this behavior
 * @return String
 */
	protected function _getValidationErrors(Model $model) {
		// Initialize array of validation errors
		$list = array();

		// Prepare a list of validation errors
		foreach ($model->validationErrors as $field => $val) {
			$list[$field] = implode(', ', $val);
		}

		// Return prepared list of errors
		return $list;
	}

/**
 * Custom validation rule for uploaded files.
 *
 * @param array   $data     CakePHP File info.
 * @param boolean $required Is this field required?
 * @return boolean
 */
	public function validateFile(array $data, $required = false) {
		// Remove first level of Array ($data['Artwork']['size'] becomes $data['size'])
		$uploadInfo = array_shift($data);

		// No file uploaded.
		if ($required && $uploadInfo['size'] == 0) {
				return false;
		}

		// Check for Basic PHP file errors.
		if ($uploadInfo['error'] !== 0) {
			return false;
		}

		// Finally, use PHP's own file validation method.
		return is_uploaded_file($uploadInfo['tmp_name']);
	}

/**
 * A workaround to provide a validation rule 'file'
 *
 * @param array $check The file to check
 * @return true        Since only validated in the frotnend, nothing to do here
 */
	public function file($check) {
		return true;
	}

/**
 * After saving load the full record from the database to
 * return to the frontend.
 *
 * @param Model   $model   Model using this behavior
 * @param boolean $created True if this save created a new record
 * @param array   $options Options passed from Model::save().
 * @return boolean          True if saving should proceed
 */
	public function afterSave(Model $model, $created, $options = array()) {
		// get all the data bancha needs for the response
		// and save it in the data property
		if ($created) {
			// just add the id
			$this->_result[$model->alias] = $model->data;
			$this->_result[$model->alias][$model->name]['id'] = $model->id;
		} else {
			// load the full record from the database
			// Setting recursive to -1 may result in an error if the virtual field uses associated data
			// On the other hand not setting the recursion to -1 has some negative performance impacts
			// $currentRecursive = $model->recursive;
			// $model->recursive = -1;
			$this->_result[$model->alias] = $model->read();
			// $model->recursive = $currentRecursive;
		}

		return true;
	}

/**
 * Returns the result record of the last save operation.
 * 
 * @param Model $model The model using this behavior
 * @return mixed       The record data of the last saved record
 * @throws BanchaException If there is not result for this model.
 */
	public function getLastSaveResult(Model $model) {
		if (empty($this->_result[$model->alias])) {

			// throw an exception for empty response
			throw new BanchaException(
				'There was nothing saved to be returned. Probably this occures because the data ' .
				'you send from Ext JS was malformed. Please use the Bancha.model.ModelName ' .
				'model to create, load and save model records. If you really have to create ' .
				'your own models, make sure that the JsonWriter "root" (Ext JS) / "rootProperty" ' .
				'(Sencha Touch) is set to "data".');
		}

		// otherwise return result, that is set in the saveFields() method
		return $this->_result[$model->alias];
	}

/**
 * Builds a field list with all defined fields.
 *
 * @param Model $model Model using this behavior
 * @return array       A list of fields
 * @throws BanchaException If the data is malformed.
 */
	protected function _buildFieldList(Model $model) {
		// Make a quick quick check if the data is in the right format
		if (isset($model->data[$model->name][0]) && is_array($model->data[$model->name][0])) {
			throw new BanchaException(
				'The data to be saved seems malformed. Probably this occures because you send ' .
				'from your own model or you one save invokation. Please use the Bancha.model.ModelName ' .
				'model to create, load and save model records. If you really have to create ' .
				'your own models, make sure that the JsonWriter "root" (Ext JS) / "rootProperty" ' .
				'(Sencha Touch) is set to "data". <br /><br />' .
				'Got following data to save: <br />' . print_r($model->data, true));
		}
		// More extensive data validation
		// For performance reasons this is just done in debug mode
		if (Configure::read('debug') == 2) {
			$valid = false;
			$fields = $model->getColumnTypes();
			// check if at least one field is saved to the database
			try {
				if (isset($model->data[$model->name]) && is_array($model->data[$model->name])) {
					foreach ($fields as $field => $type) {
						if ($field !== $model->primaryKey && array_key_exists($field, $model->data[$model->name])) {
							$valid = true;
							break;
						}
					}
				}
			} catch (Exception $e) {
				throw new BanchaException(
					'Caught exception: ' . $e->getMessage() . ' <br />' .
					'Bancha couldn\'t find any fields. This is usually because the Model is incorrectly designed. ' .
					'Check your model <br /><br /><pre>' . print_r($model->data, true) . '</pre>'
				);
			}
			if (!$valid) {
				throw new BanchaException(
					'You try to save a record, but Bancha is not able to find the data. Bancha could ' .
					'not find even one model field in the send data. Probably this occurs because you ' .
					'saved a record from your own model with a wrong configuration. Please use the ' .
					'Bancha.model.ModelName model to create, load and save model records. If ' .
					'you really have to create your own models, make sure that the JsonWriter property ' .
					'"root" (Ext JS) / "rootProperty" (Sencha Touch) is set to "data". <br /><br />' .
					'Got following data to save: <br />' . print_r($model->data, true) . '<br /><br />' .
					'For support please <a href="http://bancha.io/forum/category/1.html">write us in the forum</a>.'
				);
			}
		} //eo debugging checks

		return array_keys(isset($model->data[$model->name]) ? $model->data[$model->name] : $data);
	}

/**
 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
 *
 * @param Model $model the model using this behavior
 * @param Array $options Options passed from model::save(), see $options of model::save().
 * @return Boolean True if validate operation should continue, false to abort
 */
	public function beforeValidate(Model $model, $options = array()) {
		if ($this->_settings[$model->alias]['useOnlyDefinedFields'] && !empty($model->data[$model->name])) {
			// if not yet defined, create a field list to validate only the changes (empty records will still invalidate)
			$model->whitelist = empty($options['fieldList']) ? $this->_buildFieldList($model) : $options['fieldList']; // TODO how to not overwrite the whitelist?
		}

		// start validating data
		return true;
	}

/**
 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
 *
 * @param Model $model   Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return Boolean True if the operation should continue, false if it should abort
 */
	public function beforeSave(Model $model, $options = array()) {
		if ($this->_settings[$model->alias]['useOnlyDefinedFields']) {
			// if not yet defined, create a field list to save only the changes
			$options['fieldList'] = empty($options['fieldList']) ? $this->_buildFieldList($model) : $options['fieldList'];
		}

		// start saving data
		return true;
	}

/**
 * Saves a records, either add or edit.
 * See $this->_defaults['useOnlyDefinedFields'] for an explanation
 *
 * @param Model $model the model using this behavior
 * @param Array $data the data to save (first user argument)
 * @param Array $options the save options
 * @return Array|Boolean The result of the save operation (same as in Model->save($data))
 */
	public function saveFields(Model $model, $data = null, $options = array()) {
		// overwrite config for this commit
		$config = $this->_settings[$model->alias]['useOnlyDefinedFields'];
		$this->_settings[$model->alias]['useOnlyDefinedFields'] = true;

		// this should never be the case, cause Bancha cannot handle validation errors currently
		// We expect to automatically send validation errors to the client in the right format in version 1.1
		if ($data) {
			$model->set($data);
		}

		// try to validate data
		$success = true;
		if (!$model->validates()) {
			// prepare Sencha formatted response of validation errors on failure to validate
			$this->_result[$model->alias] = array(
				'success' => false,
				'errors' => $this->_getValidationErrors($model)
			);
			$success = false;

		} else {
			// set result with saved record
			$this->_result[$model->alias] = $model->save($model->data, $options);
			$success = !empty($this->_result[$model->alias]);
		}

		// set back
		$this->_settings[$model->alias]['useOnlyDefinedFields'] = $config;

		// return saved record data if not empty and valid, otherwise false
		return $success ? $this->_result[$model->alias] : false;
	}

/**
 * Commits a save operation for all changed data and
 * returns the result in an Sencha format
 * for return value see also getLastSaveResult()
 *
 * @param Model $model The model is always the first param (cake does this automatically)
 * @param array $data  The data to save, first function argument
 * @return mixed       The record data of the saved record
 */
	public function saveFieldsAndReturn(Model $model, $data = null) {
		// save
		$this->saveFields($model, $data);

		// return ext-formated result
		return $this->getLastSaveResult($model);
	}

/**
 * Convenience methods, just delete and then return $model->getLastSaveResult();
 *
 * @param Model $model Model using this behavior
 * @return array|boolean The latest save result
 * @throws NotFoundException If the record doesn't exist.
 */
	public function deleteAndReturn(Model $model) {
		if (!$model->exists()) {
			throw new NotFoundException(__('Invalid ' . $model->name));
		}
		$model->delete();
		return $this->getLastSaveResult($model);
	}

/**
 * Keep the result of the delete action.
 * 
 * @param Model $model Model using this behavior
 * @return void
 */
	public function afterDelete(Model $model) {
		// if no exception was thrown so far the request was successfull
		$this->_result[$model->alias] = true;
	}

/**
 * Returns an Ext JS formated array describing sortable fields
 * this is '$order' in CakePHP.
 *
 * @param Model $model Model using this behavior
 * @return array       The Sencha Touch/Ext JS formated sorters like
 *                     { property: 'name', direction: 'ASC'	}
 * @throws CakeException If the sorter data is in an malformed form.
 */
	public function getSorters(Model $model) {
		$sorters = array();

		if (empty($model->order)) {
			return $sorters;
		}

		if (is_string($model->order)) {
			$order = trim($model->order);

			if (strpos($order, '.') === false) {
				// this is just the field name
				$fieldName = $order;
				$direction = 'ASC';

			} elseif (strpos($order, ' ') === false) {
				// this has a model name and a field name, but no direction
				$modelName = strtok($order, ".");
				$fieldName = strtok(" ");
				$direction = 'ASC';
			} else {
				// this has a model name, a field name and a direction
				$modelName = strtok($order, ".");
				$fieldName = strtok(" ");
				$direction = strtoupper(substr($order, strpos($order, ' ') + 1));
			}
			array_push($sorters, array( 'property' => $fieldName, 'direction' => $direction));

		} elseif (is_array($model->order)) {
			foreach ($model->order as $key => $direction) {
				$modelName = strtok($key, ".");
				$fieldName = strtok(".");
				array_push($sorters, array( 'property' => $fieldName, 'direction' => $direction));
			}

		} else {
			throw new CakeException(
				'The CakePHP ' . $model->alias . ' model configuration for order needs to be a string ' .
				'or array, instead got ' . gettype($model->order)
			);
		}
		return $sorters;
	}

}
