<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Controller
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

// todo this should not not be necessary
App::import('Controller', 'Bancha.BanchaApp');
App::uses('BanchaException', 'Bancha.Bancha/Exception');

/**
 * Bancha Controller
 * This class exports the ExtJS API for remotable models and controller.
 * This is only internally used by the client side of Bancha.
 *
 * @package    Bancha
 * @subpackage Controller
 * @author Andreas Kern
 * @author Florian Eckerstorfer <florian@theroadtojoy.at>
 */
class BanchaController extends BanchaAppController {

	var $name = 'Bancha.Bancha'; //turns html on again
	var $autoRender = false; //we don't need a view for this
	var $autoLayout = false;
	//var $viewClass = 'Bancha.BanchaExt';


	/**
	 *  CRUD mapping between cakephp and extjs
	 * 	TODO check if the right arguments are passed
	 *
	 * @var array
	 */
	public $mapCrud = array(
			'index' => array('getAll', 0),
			'add' => array('create', 1),
			'view' => array('read', 1),
			'edit' => array('update', 1),
			'delete' => array('destroy', 1)
	);


	/**
	 * the index method is called by default by cakePHP if no action is specified,
	 * it will print the API for the Controllers which have the Bancha-
	 * Behavior set. This will not include any model meta data. to specify which
	 * model meta data should be printed you will have to pass the model names as
	 * controller parameters as in cakePHP.e.g.: http://localhost/Bancha/loadMetaData/User/Tag 
	 * will load the metadata from the models Users and Tags
	 *
	 * @return void
	 */
	public function index($metaDataForModels='') {
		
		// send as javascript
		header('Content-type: text/javascript');
	
		// get namespace
		$namespace = Configure::read('Bancha.namespace');
		if(empty($namespace)) {
			$namespace = 'Bancha.RemoteStubs'; // default
		}
		
		/**
		 * The ExtJS API array which is returned
		 *
		 * @var array
		 */
		$API = array(
			'url'		=> '/bancha.php',
			'namespace'	=> $namespace,
    		'type'		=> 'remoting',
		);



		/****** parse Models **********/

		$models = App::objects('Model');
		$banchaModels = array();

		//load all Models and add those with Banchas BanchaRemotableBehavior into $banchaModels
		foreach ($models as $model) {
			$this->loadModel($model);
			if (is_array($this->{$model}->actsAs )) {
				if (in_array('Bancha.BanchaRemotable', $this->{$model}->actsAs)) {
					array_push($banchaModels, $model);
				}
			}
		}

		// insert UID
		$API['metadata']['_UID'] = str_replace('.', '', uniqid('', true));

	    // get requested models
		if (strlen($metaDataForModels)>2) {
			if ('all' === $metaDataForModels || '[all]' === $metaDataForModels) {
			    $metaDataModels = $banchaModels;
		    } else  {
               $metaDataModels = explode(',', substr($metaDataForModels,1,-1));
		    }
        } else {
            $metaDataModels = array();
        }
		
		//load the MetaData into $API
		foreach ($metaDataModels as $mod) {
			if (!in_array($mod, $banchaModels)) {
				throw new MissingModelException($mod);
			}
			$this->{$mod}->setBehaviorModel($mod);
			$API['metadata'][$mod] = $this->{$mod}->extractBanchaMetaData();
		}

		foreach($banchaModels as $model_name) {
			// Generate controller name and load it.
			$controller_name = Inflector::pluralize($model_name) . 'Controller';
			include(APP . DS . 'Controller' . DS . $controller_name . '.php');

			if (!class_exists($controller_name))
			{
				throw new BanchaException(sprintf('Controller "%s" does not exist.', $controller_name));
			}

			// Retrieve methods using Reflection API.
			$reflection = new ReflectionClass($controller_name);
			$methods = $reflection->getMethods();

			$API['actions'][$model_name] = array();

			foreach ($methods as $method)
			{
				// Case 1: CRUD method
				if (isset($this->mapCrud[$method->name])) {
					array_push($API['actions'][$model_name], array(
						'name'	=> $method->name,
						'len'	=> $method->getNumberOfParameters(),
					));
				}
				// Case 2: Form handler
				elseif ('add' === $method->name || 'edit' == $method->name)
				{
					array_push($API['actions'][$model_name], array(
						'name'			=> 'submit',
						'len' 			=> 1,
						'formHandler'	=> true,
					));
				}
				// Case 3: Bancha Remoteable
				elseif (preg_match('/@banchaRemotable/', $method->getDocComment()))
				{
					array_push($API['actions'][$model_name], array(
						'name'	=> $method->name,
						'len'	=> $method->getNumberOfParameters(),
					));
				}
			} // end foreach models

		}

		// add Bancha controller functions
		$API['actions']['Bancha'] = array(
			array('name'=>'loadMetaData', 'len'=>1)
		);
		
		$this->set('API', $API);
		$remoteApiNamespace = Configure::read('Bancha.remote_api');
		if(empty($remoteApiNamespace)) {
			$remoteApiNamespace = 'Bancha.REMOTE_API';
		}
		print("Ext.ns('Bancha'); ".$remoteApiNamespace." =" . json_encode($API));
		//$this->render(null, 'ajax', null); //removes the html
	}

	/**
	 * loadMetaData returns the Metadata of the models passed as an argument or 
	 * in params['pass'] array which is created by cakephp from the arguments 
	 * passed in the url. e.g.: http://localhost/Bancha/loadMetaData/User/Tag 
	 * will load the metadata from the models Users and Tags
	 * 
	 * @return array 
	 */
	public function loadMetaData() {
		$models = array();
		if(isset($this->params['data'][0])) {
			$models = $this->params['data'][0];
		}
		
		if ($models == null) {
			return;
		}

		if ( is_string($models)) {
			$models = array($models);
		}
		$modelMetaData = array();
		foreach($models as $mod) {
			$mod =  Inflector::Singularize($mod);
			$mod = ucfirst($mod);
			$this->loadModel($mod);
			$this->{$mod}->setBehaviorModel($mod);
			$modelMetaData[$mod] = $this->{$mod}->extractBanchaMetaData();

		}
		return $modelMetaData;
	}
}
