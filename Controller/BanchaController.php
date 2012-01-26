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
App::uses('BanchaApi', 'Bancha.Bancha');

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
	 * @param string $metaDataForModels Models that should be exposed through the Bancha API. Either all or [all] for
	 *                                  all models or a comma separated list of models.
	 * @return void
	 */
	public function index($modelFilter='') {
		$modelFilter = urldecode($modelFilter);
		$banchaApi = new BanchaApi();
		
		// send as javascript
		header('Content-type: text/javascript');
	
		// get namespace
		$namespace = Configure::read('Bancha.namespace');
		if(empty($namespace)) {
			$namespace = 'Bancha.RemoteStubs'; // default
		}
		
		$remotableModels = $banchaApi->getRemotableModels();
        $requestedModels = $banchaApi->filterRemotableModels($remotableModels, $modelFilter);
		
		$api = array(
			'url'		=> '/bancha.php',
			'namespace'	=> $namespace,
    		'type'		=> 'remoting',
    		'metadata'	=> $banchaApi->getMetadata($requestedModels),
    		'actions'	=> array_merge_recursive(
				$banchaApi->getRemotableModelActions($remotableModels),
				$banchaApi->getRemotableMethods(),
				array('Bancha' => array(
					array(
						'name'	=> 'loadMetaData',
						'len'	=> 1,
					),
				))
			)
		);
		
		$remoteApiNamespace = Configure::read('Bancha.remote_api');
		if(empty($remoteApiNamespace)) {
			$remoteApiNamespace = 'Bancha.REMOTE_API';
		}

		$this->set('remoteApiNamespace', $remoteApiNamespace);
		$this->set('banchaApi', $api);
		$this->render();
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
