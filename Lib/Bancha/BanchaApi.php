<?php

class BanchaApi {

	private $uses = array();
	
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
				if (in_array('Bancha.BanchaRemotable', $model->actsAs)) {
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
	 * @param  string $filter Explicit list of remotable models. Can be "all", "[all]" or "[Model1,Model2,...]" (without 
	 *                        quotes).
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
		$filteredModels = array_map('trim', explode(',', substr($filter, 1, -1)));
		foreach ($filteredModels as $filteredModel)
		{
			if (!in_array($filteredModel, $models))
			{
				throw new MissingModelException($filteredModel);
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
			$model->setBehaviorModel($modelClass);
			$metadata[$modelClass] = $model->extractBanchaMetaData();
		}
		$metadata['_UID'] = str_replace('.', '', uniqid('', true));
		return $metadata;
	}

	/**
	 * Loads the model with the given name and returns an instance.
	 *
	 * @param  string   $modelClass Name of a model
	 * @return AppModel             Instance of the model with the given class name.
	 * @throws MissingModelException if the model class does not exist.
	 */
	public function loadModel($modelClass) {
		list($plugin, $modelClass) = pluginSplit($modelClass, true);

		$model = ClassRegistry::init(array(
			'class' => $plugin . $modelClass, 'alias' => $modelClass, 'id' => null
		));
		if (!$model) {
			throw new MissingModelException($modelClass);
		}
		return $model;
	}

}

