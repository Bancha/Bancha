<?php

/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha.Controller.Component
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('PaginatorComponent', 'Controller/Component');
App::uses('BanchaException', 'Bancha.Bancha/Exception');

/**
 * BanchaPaginatorComponent
 * 
 * This class extends the default Paginator component to also support
 * remote filtering from inside any ExtJS/Sencha Touch store with
 * remoteFiltering:true and a Bancha model.
 *
 * @package    Bancha.Controller.Component
 * @author     Roland Schuetz <mail@rolandschuetz.at>
 */
class BanchaPaginatorComponent extends PaginatorComponent {

/**
 * Define here on which fields it's allowed to filter data.
 * In cakephp debugging mode with the Bancha dev version, Bancha will 
 * display an error if you are filtering for unallowed filter
 * 
 * 
 * Possible options: 
 *  - 'all'  
 *     Please choose wisely, defining 'all' could lead to potenzial
 *     security issues since hackers could expose more data then 
 *     expected with standard MySQL-like attacks. E.g. filtering
 *     by password with regex or filtering other hidden information.
 * 
 *  - 'associations'
 *    This exposes only fields where there is an association defined
 *    in the model, so for article belongsTo user filtering by user_id
 *    is allowed.
 * 
 *  - array('field1', 'field2') -> This will be internally transformed to below one
 *    array('User.field1', 'User.field2')
 *    You can specifically define which fields you want to allow filtering upon.
 *
 *  - 'none' -> This will be internally transformed to an empty array
 *    No filtering is allowed.
 *
 */
	protected $allowedFilters = 'associations';

/**
 * A reference to the instantiating controller object. 
 * This is setup by the PaginatorComponent itself.
 *
 * @access private
 * @var Controller class
 */
	protected $Controller;

/**
 * Main execution method. Handles validating of allowed filter constraints.
 *
 * @param Controller $controller A reference to the instantiating controller object
 * @throws BanchaException 
 * @return void
 */
	public function startup(Controller $controller) {
		if(!isset($controller->request->params['isBancha']) || !$controller->request->params['isBancha']) {
			// this is not a Bancha request, so nothing for us to do here
			return;
		}

		// set the controller-specific options if defined
		$this->_setSettings($this->settings);
	}

/**
 * Handles automatic pagination of model records. 
 * 
 * The BanchaPaginatorComponents extends the default
 * behavior by supporting remote filtering on Bancha 
 * requests if the $allowedFilters property allows it.
 *
 * @param mixed $object Model to paginate (e.g: model instance, or 'Model', or 'Model.InnerModel')
 * @param mixed $scope Additional find conditions to use while paginating
 * @param array $whitelist List of allowed fields for ordering.  This allows you to prevent ordering
 *   on non-indexed, or undesirable columns.
 * @return array Model query results
 * @throws MissingModelException
 * @throws BanchaException
 */
    public function paginate($object = null, $scope = array(), $whitelist = array()) {

    	// bancha-specific access-restriction logic
		if(isset($this->Controller->request->params['isBancha']) && $this->Controller->request->params['isBancha']) {
			// this is a Bancha request, apply the allowed filters
			$this->whitelist[] = 'conditions';

			// filter given conditions-array and apply it to our pagination
			$remoteConditions = $this->sanitizeFilterConditions($this->allowedFilters, $this->Controller->request->named['conditions']);
			$scope = array_merge($remoteConditions, $scope);
		}

		return parent::paginate($object, $scope, $whitelist);
	}

/**
 * Attempts to introspect the correct values for object properties.
 * @access private
 * @param Array $settings An array of configuratuions for this component
 * @throws BanchaException
 * @return void
 */
	private function _setSettings($settings) {

		// override defaults by component configs
		foreach ($settings as $key => $value) {
			if(property_exists($this, $key)) {
				$this->{$key} = $value; // override
			}
		}

		// allowedFilters is already set, now verify correctness
		$this->setAllowedFilters($this->allowedFilters);
	}

/**
 * Change the allowedFilter at run-time. This function will santizise and may throw an
 * error if the configuration is malformed.
 * @param String/String[] $allowedFilters the new value for the allowedFilters property
 * @throws BanchaException
 * @return void
 */
	public function setAllowedFilters($allowedFilters) {

		// check if the allowedFilters is configured correctly
		if(!isset($allowedFilters)) {
			throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration needs to be set.');
		}
		if(is_string($allowedFilters)) {
			if(!in_array($allowedFilters, array('all', 'associations', 'none'))) {
				throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration is a unknown string value: ' . $allowedFilters);
			}

			// transform 'none' to an empty array
			if($allowedFilters == 'none') {
				$allowedFilters = array();
			}
		} else if(is_array($allowedFilters)) {

			// check if the array is in the form array('field1','field2') and if so transform
			if(count($allowedFilters)!=0) {
				if(strpos($allowedFilters[0], '.') === FALSE) {
					$modelName = $this->Controller->modelClass; // the name of the primary model
					foreach($allowedFilters as $key=>$value) {
						$allowedFilters[$key] = $modelName . '.' . $value; // transform to Model.field
					}
				}

				// in debug mode check if the field are really existing
				if(Configure::read('debug') == 2) {
					foreach($allowedFilters as $key=>$value) {
						$parts = explode('.',$value);
						if(count($parts) != 2) {
							throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration could not be recognized at array position '.$key.', value: '.$value);
						}
						$modelName = $parts[0];
						$fieldName = $parts[1];

						if(!is_object($this->Controller->{$modelName})) {
							print_r($this->Controller->{$modelName});
							throw new BanchaException('The '.$this->Controller->name.'Controller is missing the model '.$modelName.', but has a configuration for this model in BanchaPaginatorComponent::allowedFilters. Please make sure to define the controllers uses property or use the beforeFilter for loading.');
						}
						if($this->Controller->{$modelName}->virtualFields && $this->Controller->{$modelName}->virtualFields[$fieldName]) {
							throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration allows filtering on '.$value.', but this is a virtual field cakephp can\'t handle constraints on them.');
						}

						$schema = $this->Controller->{$modelName}->schema();
						if(!isset($schema[$fieldName])) {
							// this field doesn't exist in the database
							throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration allows filtering on '.$value.', but this is field doesn\'t exist in the models schema.');
						}
					}
				}
			}
			// check if all array fields are matching the model
		} else {
			throw new BanchaException('The BanchaPaginatorComponent::allowedFilters configuration needs to be either a string or an array.');
		}

		$this->allowedFilters = $allowedFilters;
	}

/**
 * This functions loops through all filter conditions and check if the are valid
 * according to the {@link allowedFilter} property.
 *
 * @param  Array|String $allowedFilters the $allowedFilters configuration for this pagination request
 * @param  Array $conditions the given remote filter conditions to santisize
 * @throws BanchaException
 * @return Array the allowed filter conditions
 */
	private function sanitizeFilterConditions($allowedFilters, $conditions) {
		if($allowedFilters == 'all') {
			return $conditions;
		}

		// check each condition and filter unalloweds out
		if($allowedFilters == 'associations') {
			// check each condition individualy
			foreach($conditions as $field=>$value) {
				list($modelName, $fieldName) = explode('.', $field);

				// look though all associations if we can find the field name as foreign key
				$model = $this->Controller->{$modelName};
				$assocs = $model->Behaviors->BanchaRemotable->getAssociated($model); // use the Bancha-specific method to get the foreign keys
				$valid = false;
				foreach($assocs as $assoc) {
					if($assoc['foreignKey'] == $fieldName) {
						$valid = true; // this is a valid association key
						break;
					}
				}

				if(!$valid) {
					if(Configure::read('debug') == 2) {
						throw new BanchaException('The last ExtJS/Sencha Touch request tried to filter the by '.$field.', which is not allowed according to the '.$this->Controller->name.' BanchaPaginatorComponent::allowedFilters configuration.');
					} else {
						// we are not in debug mode where we want to throw an exception, so just ignore this filtering
						delete($conditions[$field]);
					}
				}
			}
			return $conditions;
		}

		// allowedFilters is an array
		// check each condition individually
		foreach($conditions as $field=>$value) {
			if(!in_array($field, $allowedFilters)) {
				if(Configure::read('debug') == 2) {
					throw new BanchaException('The last ExtJS/Sencha Touch request tried to filter the by '.$field.', which is not allowed according to the '.$this->Controller->name.' BanchaPaginatorComponent::allowedFilters configuration.');
				} else {
					// we are not in debug mode where we want to throw an exception, so jsut ignore this filtering
					delete($conditions[$field]);
				}
			}
		}

		return $conditions;
	}
}
