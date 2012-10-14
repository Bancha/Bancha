<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha.Controller.Component
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('Component', 'Controller');
App::uses('BanchaException', 'Bancha.Bancha/Exception');

/**
 * Bancha Controller
 * This class exports the ExtJS API for remotable models and controller.
 * This is only internally used by the client side of Bancha.
 *
 * @package    Bancha.Controller.Component
 * @author     Roland Schuetz <mail@rolandschuetz.at>
 */
class BanchaComponent extends Component {

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
 * A reference to the instantiating controller object
 * @var Controller class
 */
	private $Controller;

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

		// keep a reference to the controller
		$this->Controller = $controller;

		// set the controller-specific optiosn is defined
		$this->_setSettings($this->settings);

		// tell the PaginationComponent to use our conditions
		$this->Controller->Components->load('Paginator')->whitelist[] = 'conditions';
	}

/**
 * Attempts to introspect the correct values for object properties.
 * @param Array $settings An array of configuratuions for this component
 * @throws BanchaException
 * @return void
 */
	private function _setSettings($settings) {

		// override defaults by user configs
		foreach ($settings as $key => $value) {
			if(property_exists($this, $key)) {
				$this->{$key} = $value; // override
			}
		}

		// fire the setter for allowedFilters
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
			throw new BanchaException('The BanchaComponent::allowedFilters configuration needs to be set.');
		}
		if(is_string($allowedFilters)) {
			if(!in_array($allowedFilters, array('all', 'associations', 'none'))) {
				throw new BanchaException('The BanchaComponent::allowedFilters configuration is a unknown string value: ' . $allowedFilters);
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
							throw new BanchaException('The BanchaComponent::allowedFilters configuration could not be recognized at array position '.$key.', value: '.$value);
						}
						$modelName = $parts[0];
						$fieldName = $parts[1];

						if(!is_object($this->Controller->{$modelName})) {
							print_r($this->Controller->{$modelName});
							throw new BanchaException('The '.$this->Controller->name.'Controller is missing the model '.$modelName.', but has a configuration for this model in BanchaComponent::allowedFilters. Please make sure to define the controllers uses property or use the beforeFilter for loading.');
						}
						if($this->Controller->{$modelName}->virtualFields && $this->Controller->{$modelName}->virtualFields[$fieldName]) {
							throw new BanchaException('The BanchaComponent::allowedFilters configuration allows filtering on '.$value.', but this is a virtual field cakephp can\'t handle constraints on them.');
						}

						$schema = $this->Controller->{$modelName}->schema();
						if(!isset($schema[$fieldName])) {
							// this field doesn't exist in the database
							throw new BanchaException('The BanchaComponent::allowedFilters configuration allows filtering on '.$value.', but this is field doesn\'t exist in the models schema.');
						}
					}
				}
			}
			// check if all array fields are matching the model
		} else {
			throw new BanchaException('The BanchaComponent::allowedFilters configuration needs to be either a string or an array.');
		}


		// filter conditions-array and set result
		$this->allowedFilters = $this->sanitizeFilterConditions($allowedFilters);
	}

/**
 * This functions loops through all filter conditions and check if the are valid
 * according to the {@link allowedFilter} property
 * @throws BanchaException
 * @return void
 */
	private function sanitizeFilterConditions($allowedFilters) {
		if($allowedFilters == 'all') {
			return $allowedFilters;
		}

		// check each condition and filter unalloweds out
		if($allowedFilters == 'associations') {
			// check each condition individualy
			foreach($this->Controller->request->named['conditions'] as $field=>$value) {
				list($modelName, $fieldName) = explode('.', $field);

				// look though all associations if we can find the field name as foreign key
				$assocs = $this->Controller->{$modelName}->Behaviors->BanchaRemotable->getAssociated(); // use the Bancha-specific method to get the foreign keys
				$valid = false;
				foreach($assocs as $assoc) {
					if($assoc['foreignKey'] == $fieldName) {
						$valid = true; // this is a valid association key
						break;
					}
				}

				if(!$valid) {
					if(Configure::read('debug') == 2) {
						throw new BanchaException('The last ExtJS/Sencha Touch request tried to filter the by '.$field.', which is not allowed according to the '.$this->Controller->name.' BanchaComponent::allowedFilters configuration.');
					} else {
						// we are not in debug mode where we want to throw an exception, so just ignore this filtering
						delete($this->Controller->request->paginate['conditions'][$field]);
					}
				}
			}
			return $allowedFilters;
		}

		// allowedFilers is an array
		// check each condition individually
		foreach($this->Controller->request->named['conditions'] as $field=>$value) {
			if(!in_array($field, $allowedFilters)) {
				if(Configure::read('debug') == 2) {
					throw new BanchaException('The last ExtJS/Sencha Touch request tried to filter the by '.$field.', which is not allowed according to the '.$this->Controller->name.' BanchaComponent::allowedFilters configuration.');
				} else {
					// we are not in debug mode where we want to throw an exception, so jsut ignore this filtering
					delete($this->Controller->request->paginate['conditions'][$field]);
				}
			}
		}

		return $allowedFilters;
	}
}
