<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Lib
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.3
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * BanchaApi
 * A Helper class for building the bancha-enhanced Ext.Direct API.
 *
 * @package       Bancha
 * @subpackage    Lib
 */
class BanchaApi {

	/**
	 *  CRUD mapping between cakephp and extjs
	 * 	TODO check if the right arguments are passed
	 *
	 * @var array
	 */
	protected $crudMapping = array(
		'index'		=> array('name' => 'getAll',	'len' => 0),
		'add'		=> array('name' => 'create',	'len' => 1),
		'view'		=> array('name' => 'read',		'len' => 1),
		'edit'		=> array('name' => 'update',	'len' => 1),
		'delete'	=> array('name' => 'destroy',	'len' => 1),
	);

	/**
	 * Returns a list of all models that marked to act as BanchaRemotable.
	 *
	 * @return array List of all remotable models.
	 */
	public function getRemotableModels()
	{
		$models = App::objects('Model');
		$remotableModels = array();

		// load all Models and add those with Banchas BanchaRemotableBehavior into $remotableModels
		foreach ($models as $modelClass) {
			$model = $this->loadModel($modelClass);
			if (isset($model->actsAs) && is_array($model->actsAs)) {
				// check if it is remotable (first when a AppModel behavior is also defined, second when not)
				if (array_key_exists('Bancha.BanchaRemotable', $model->actsAs) || in_array('Bancha.BanchaRemotable', $model->actsAs)) {
					$remotableModels[] = $modelClass;
				}
			}
		}
		return $remotableModels;
	}

	/**
	 * Returns the $models array if the filter is "all" or "[all]" (without quotes), else splits up the comma separated 
	 * list of models given in $filter. If $filter is NULL or an empty string an empty array is returned.
	 *
	 * @param  array  $models List of remotable models
	 * @param  string/array $filter Explicit list of remotable models. Can be "all", "[all]" or "[Model1,Model2,...]" (without 
	 *                        quotes). Or an array of models.
	 * @return array          Filtered list of remotable models.
	 */
	public function filterRemotableModels($models, $filter)
	{
		if (!$filter) {
			return array();
		}
		if ('all' === $filter || '[all]' === $filter) {
			return $models;
		}

		// First remove the [ and ], then split by comma and trim each element.
		if (is_string($filter) && false !== strpos($filter, '[') && false !== strpos($filter, ']'))
		{
			$filter = substr($filter, 1, -1);
		}
		
		// transform string to array
		$filteredModels = is_string($filter) ? explode(',', $filter) : $filter;
		
		// trim to prevent errors from unclean developer code
		$filteredModels = array_map('trim', $filteredModels);
		
		// check if they really exist
		foreach ($filteredModels as $filteredModel)
		{
			if (!in_array($filteredModel, $models))
			{
				throw new MissingModelException(array('class' => $filteredModel));
			}
		}
		return $filteredModels;
	}

	/**
	 * Returns the metadata for the given models.
	 *
	 * @param  array $models List of remotable models.
	 * @return array         Associative array with metadata of the given models.
	 */
	public function getMetadata($models)
	{
		$metadata = array();
		foreach ($models as $modelClass) {
			$model = $this->loadModel($modelClass);
			$metadata[$modelClass] = $model->extractBanchaMetaData($modelClass);
		}
		$metadata['_UID'] = str_replace('.', '', uniqid('', true));
		$metadata['_CakeDebugLevel'] = Configure::read('debug');
		return $metadata;
	}

	/**
	 * Returns the name of the controller based on the given name of the model.
	 *
	 * @param  string $modelClass Name of the model
	 * @return string             Name of the controller class.
	 */
	public function getControllerClassByModelClass($modelClass) {
		$controllerClass = Inflector::pluralize($modelClass) . 'Controller';
		// load to check if the controller exists.
		$this->loadController($controllerClass);
		return $controllerClass;
	}

	/**
	 * Returns all CRUD actions of the given controller mapped into the ExtJS format.
	 *
	 * @param  string $controllerClass Name of the controller.
	 * @return array                   Array with mapped CRUD actions. Each action is an array where the first element
	 *                                 is the name and the second element is the number of arguments. If the method is
	 *                                 a form handler, the elements are named "name", "len" and "formHandler".
	 */
	public function getCrudActionsOfController($controllerClass) {
		$methods = $this->getClassMethods($controllerClass);

		$addFormHandler = false;
		$crudActions = array();
		foreach ($methods as $method) {
			if ('add' === $method->name || 'edit' == $method->name) {
				$addFormHandler = true;
			}
			if (isset($this->crudMapping[$method->name])) {
				$crudActions[] = $this->crudMapping[$method->name];
			}
		}

		// If this controller supports a form handler submit, add it to the crud actions.
		if ($addFormHandler) {
			$crudActions[] = array(
				'name'			=> 'submit',
				'len' 			=> 1,
				'formHandler'	=> true,
			);
		}

		return $crudActions;
	}

	/**
	 * Returns all actions marked as @banchaRemotable from all controllers.
	 *
	 * @return array Remotable methods in the same format is in getCrudActionsOfController().
	 */
	public function getRemotableMethods() {
		$remotableMethods = array();

		$controllers = App::objects('Controller');
		foreach ($controllers as $controllerClass) {
			$this->loadController($controllerClass);
			$modelClass = Inflector::singularize(str_replace('Controller', '', $controllerClass));

			$methods = $this->getClassMethods($controllerClass);
			foreach ($methods as $method) {
				if (preg_match('/@banchaRemotable/', $method->getDocComment())) {
					$remotableMethods[$modelClass][] = array(
						'name'	=> $method->name,
						'len'	=> $method->getNumberOfParameters(),
					);
				}		 
			} // foreach methods
		} // foreach controllers

		return $remotableMethods;
	}

	public function getRemotableModelActions($remotableModels)
	{
		$actions = array();
		foreach ($remotableModels as $remotableModel) {
			$actions[$remotableModel] = $this->getCrudActionsOfController(
				$this->getControllerClassByModelClass($remotableModel)
			);
		}
		return $actions;
	}

	/**
	 * Loads the model with the given name and returns an instance.
	 *
	 * @param  string   $modelClass Name of a model
	 * @return AppModel             Instance of the model with the given class name.
	 * @throws MissingModelException if the model class does not exist.
	 */
	protected function loadModel($modelClass) {
		list($plugin, $modelClass) = pluginSplit($modelClass, true);

		$model = ClassRegistry::init(array(
			'class' => $plugin . $modelClass, 'alias' => $modelClass, 'id' => null
		));
		if (!$model) {
			throw new MissingModelException(array('class' => $modelClass));
		}
		return $model;
	}

	/**
	 * Loads the controller and throws an exception if it does not exist.
	 *
	 * @param  string $controllerClass Name of the controller to load.
	 * @return void
	 */
	protected function loadController($controllerClass) {	
		if(!file_exists(APP . DS . 'Controller' . DS . $controllerClass . '.php')) {
			throw new MissingControllerException(array('class' => $controllerClass));
		}
		
		include_once APP . DS . 'Controller' . DS . $controllerClass . '.php';

		if (!class_exists($controllerClass)) {
			throw new MissingControllerException(array('class' => $controllerClass));
		}
	}

	protected function getClassMethods($class) {
		$reflection = new ReflectionClass($class);
		return $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
	}
}

